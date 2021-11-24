<?php
declare(strict_types=1);

namespace App\Controller;

use App\Constants\ResponseCode;
use App\Exception\ServiceException;
use App\Model\Msg;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Context;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{

    /**
     * 组装消息
     * @param $msg
     * @param int $sourceId
     * @param int $receiveId
     * @param int $type
     * @return false|string
     * @author: zhangkaiyue
     * Time: 2021/5/17   10:40
     */
    private static function getMsg($msg, $sourceId = 0, $receiveId = 0, $type = 0)
    {
        return \json_encode([
            'body' => $msg,
            'source_id' => $sourceId,
            'receive_id' => $receiveId,
            'type' => $type
        ]);
    }

    public function onMessage($server, Frame $frame): void
    {
        //接收消息
        $sendData = json_decode($frame->data, true);
         // 中间件：验证接收人ID、禁言、关键词筛选、搜索问题
        $user = Context::get('user');
        $userType = Context::get('user_type');
        $websocketServer = Context::get('server');
        $bindKey = config('app.bind.key');
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        if($userType == 'admin') {
            // 发送消息给第三方
            if(!empty($sendData['receive_id'])){
                $userConnectKey = config('app.connect.key') . '_user';
                if($userInfo = $redis->hGet($userConnectKey, $sendData['receive_id'])){
                    $userInfo = json_decode($userInfo, true);
                    $msg = self::getMsg($sendData['body'], $user->id, $sendData['receive_id']);
                    $websocketServer->push($userInfo['fd'], $msg);
                    // 记录消息
                    Msg::insertMsg($sendData['body'], 0, $sendData['receive_id']);
                }
            }
        }else{
            //发送给客服
            $userBindAdminId = $redis->zScore($bindKey, $user->id);

            if(!empty($userBindAdminId)){
                $adminConnectKey = config('app.connect.key') . '_admin';
                $adminInfo = $redis->hGet($adminConnectKey, (string) $userBindAdminId);
                $adminInfo = json_decode($adminInfo, true);
                $msg = self::getMsg($sendData['body'], $user->id, $userBindAdminId);
                $websocketServer->push($adminInfo['fd'], $msg);
            }

            // 记录消息
            Msg::insertMsg($sendData['body'], $user->id, 0);
        }

    }

    public function onClose($server, int $fd, int $reactorId): void
    {

        $user = Context::get('user');
        $userType = Context::get('user_type');

        $connectKey = config('app.connect.key');
        $key = $connectKey . ($userType == 'admin' ? '_admin' : '_user');
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);

        //删除登录状态
        $redis->hDel($key, (string)$user->id);

        $bindKey = config('app.bind.key');
        if($userType == 'admin') {
            //通知用户下线
            $bindUserIds = $redis->zRangeByScore($bindKey, (string) $user->id, (string) $user->id);
            foreach ($bindUserIds as $key => $value) {
                //换绑为0
                $redis->zAdd($bindKey, 0, $value);
                //通知
                $userConnectKey = config('app.connect.key') . '_user';
                $userInfo = $redis->hGet($userConnectKey, $value);
                $userInfo = json_decode($userInfo, true);
                $websocketServer = Context::get('server');
                $msg = self::getMsg('你的客服'. $user->nickname . '下线了~~~', $user->id, $value);
                $websocketServer->push($userInfo['fd'], $msg);
            }
            //定时判断是否有未绑定的用户
        }else{
            $userBindAdminId = $redis->zScore($bindKey, $user->id);
            // 删除绑定关系
            $redis->zRem($bindKey, $user->id);
            if(!empty($userBindAdminId)){
                // 通知管理员
                $adminConnectKey = config('app.connect.key') . '_admin';
                $adminInfo = $redis->hGet($adminConnectKey, (string) $userBindAdminId);
                $adminInfo = json_decode($adminInfo, true);
                $websocketServer = Context::get('server');
                $msg = self::getMsg('你的客户'. $user->nickname . '下线了~~~', $user->id, $userBindAdminId);
                $websocketServer->push($adminInfo['fd'], $msg);
            }

        }

    }

    public function onOpen($server, Request $request): void
    {
        // √中间件鉴权
        // 判断当前身份
        $user = Context::get('user');
        $userType = Context::get('user_type');
        Context::set('server', $server);
        // 记录连接状态
        $connectKey = config('app.connect.key');
        $key = $connectKey . ($userType == 'admin' ? '_admin' : '_user');
        $container = ApplicationContext::getContainer();
        $redis = $container->get(Redis::class);
        //是否已经登录
        if ($redis->hExists ($key, (string) $user->id)){
            //关闭旧连接
            $lastUserInfo = $redis->hGet($key, (string)$user->id);
            $redis->hDel($key, (string)$user->id);
            $lastUserInfo = json_decode($lastUserInfo, true);
            $msg = self::getMsg('有人顶你的号~~~', 0);
            $server->push($lastUserInfo['fd'], $msg);
            $server->close($lastUserInfo['fd']);        //关闭连接
        }
        $redis->hSet($key, (string)$user->id, json_encode([
            'fd' => $request->fd,
            'nickname' => $user->nickname,
        ]));
        $bindKey = config('app.bind.key');
        if($userType == 'admin'){
            //绑定用户
            //获取所有未绑定的用户，获取消息--绑定ID为0的

            $userList = $redis->zRangeByScore($bindKey, '0', '+inf');
            if(!empty($userList)){
                foreach ($userList as $value){
                    $redis->zAdd($bindKey, $user->id, $value);
                }
            }
            $msg = self::getMsg('登陆成功,连接用户'. count($userList), $user->id);
            $server->push($request->fd, $msg);


        }else{
            // 算法绑定客服
                //是否已绑定客服，绑定|客服是否在线
            $adminKey = $connectKey . '_admin';
            $adminList = $redis->hKeys($adminKey);
            $adminId = 0;
            if($adminList) {
                //随机取id--待做：根据配置多模式获取adminId
                $adminId = $adminList[array_rand($adminList)];
            }
            if(!$redis->zAdd($bindKey, $adminId, $user->id)){
                throw new ServiceException(ResponseCode::VALIDATE_ERROR, 'redis-zadd-error');
            }
            $msg = self::getMsg('绑定管理员成功,id为：' . $adminId, $adminId, $user->id);
            $server->push($request->fd, $msg);
            if(!empty($msgList)) {
                foreach ($msgList as $value) {
                    $server->push($request->fd, self::getMsg($value->body, $value->source_id, $value->receive_id));
                }
            }

        }
    }
}
