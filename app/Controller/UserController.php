<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constants\ResponseCode;
use App\Request\UserLoginRequest;
use App\Service\UserService;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use Hyperf\WebSocketServer\Context;

/**
 */
class UserController extends AbstractController
{

    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * @param UserLoginRequest $request
     * @return \Psr\Http\Message\ResponseInterface
     * @author: zhangkaiyue
     * Time: 2021/4/27   14:10
     */
    public function login(UserLoginRequest $request)
    {
        $validateData = $request->validated();
        $data = $this->userService->login($validateData['username'], $validateData['password']);
        return $this->generateResponse(ResponseCode::SUCCESS, '', $data);
    }

    public function register()
    {

    }

    public function getInfo()
    {
        $user = Context::get('user');
        unset($user->password);
        return $this->generateResponse(ResponseCode::SUCCESS, '', $user);
    }

    public function getSession()
    {
        //获取用户最近聊天记录
        $user = Context::get('user');
        $userType = Context::get('user_type');
        $bindKey = config('app.bind.key');
        $sessions = [];
        if($userType == 'admin'){
            // 获取历史聊天记录
            // --获取绑定用户的聊天记录
            $container = ApplicationContext::getContainer();
            $redis = $container->get(Redis::class);
            $userList = $redis->zRangeByScore($bindKey, '0', '+inf');
            $msgList = Db::table('msg')->select('source_id', 'body', 'type' ,'create_time', 'receive_id')
                ->where(function ($query) use ($userList){
                    $query->where('receive_id', 0)->whereIn('source_id', $userList);
                })->orWhere(function ($query) use ($userList){
                    $query->where('source_id', 0)->whereIn('receive_id', $userList);
                })->orderByDesc('create_time')->limit(50)->get();
            $userListInfo = Db::table('user')->whereIn('id', $userList)->select('id','nickname','img')->get();
            foreach ($userListInfo as $value){
                $sessions[$value->id] = [
                    'id' => $value->id,
                    'user' => ['name' => $value->nickname, 'img' => $value->img],
                    'messages' => []
                ];
            }
            if(!empty($msgList)) {
                foreach ($msgList as $value) {
                    //发送的信息
                    if($value->source_id == 0){
                        $sessions[$value->receive_id]['messages'][] = [
                            'content' => $value->body,
                            'date' => $value->create_time,
                            'self' => true
                        ];
                    }elseif ($value->receive_id == 0){
                        $sessions[$value->source_id]['messages'][] = [
                            'content' => $value->body,
                            'date' => $value->create_time,
                            'self' => false
                        ];
                    }
                }
            }
        }else{
            // 获取历史聊天记录
            $sessions[0] = [
                'id' => 0,
                'user' => ['name' => '客服', 'img' => './assets/images/1.jpg'],
                'messages' => []
            ];
            $msgList = Db::table('msg')->select('source_id', 'receive_id', 'body', 'type' ,'create_time')
                ->where('receive_id', $user->id)->orWhere('source_id', $user->id)
                ->orderByDesc('create_time')->limit(20)->get();
            if(!empty($msgList)) {
                foreach ($msgList as $value) {
                    //发送的信息
                    if($value->source_id == $user->id){
                        $sessions[0]['messages'][] = [
                            'content' => $value->body,
                            'date' => $value->create_time,
                            'self' => true
                        ];
                    }elseif ($value->receive_id == $user->id){
                        $sessions[0]['messages'][] = [
                            'content' => $value->body,
                            'date' => $value->create_time,
                            'self' => false
                        ];
                    }
                }
            }
        }
        //重新整理排序
        foreach ($sessions as $key => $value) {
            $sessions[$key]['messages'] = array_reverse($value['messages']);
        }
        return $this->generateResponse(ResponseCode::SUCCESS, '', array_values($sessions));

    }
}
