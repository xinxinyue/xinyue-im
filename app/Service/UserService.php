<?php
namespace App\Service;

use App\Constants\ResponseCode;
use App\Exception\ServiceException;
use App\Model\Admin;
use App\Model\User;
use Firebase\JWT\JWT;
use Hyperf\Di\Annotation\Inject;

class UserService
{
    /**
     * @Inject
     * @var JWT
     */
    private $jwt;

    public function getInfoById(int $id)
    {
        $userInfo = User::query()->select('id', 'username', 'password', 'nickname', 'img')->where('id', $id)->first();
        return $userInfo;
    }

    public function getAdminInfoById(int $id)
    {
        $userInfo = Admin::query()->select('id', 'nickname', 'img')->where('id', $id)->first();
        return $userInfo;
    }

    public function getInfoByUsername(string $username)
    {
        $userInfo = User::query()->select('id', 'username', 'password', 'nickname')->where('username', $username)->first();
        return $userInfo;
    }

    /**
     * @param $username
     * @param $password
     * @return string
     * @author: zhangkaiyue
     * Time: 2021/4/26   17:36
     */
    public function login($username, $password)
    {
        $hashPassword = $this->encryptPassword($password);

        $user = $this->getInfoByUsername($username);
        if(empty($user)) {
            throw new ServiceException(ResponseCode::VALIDATE_ERROR,'账户错误');
        }
        if($hashPassword != $user->password) {
            throw new ServiceException(ResponseCode::VALIDATE_ERROR,'密码错误');
        }

        $token = $this->getToken($user->id);
        return $token;
    }

    /**
     * 客服登录
     * @param $id
     * @return string
     * @author: zhangkaiyue
     * Time: 2021/5/13   10:33
     */
    public function adminLogin($id)
    {

        $admin = Admin::query()->select('id', 'nickname')->where('id', $id)->first();
        if(empty($admin)) {
            throw new ServiceException(ResponseCode::VALIDATE_ERROR,'账户错误');
        }

        $token = $this->getToken($admin->id, 'admin');
        return $token;
    }

    protected function encryptPassword(string $password)
    {
        if('' == $password){
            return '';
        }
        $salt = config('app.auth.password_hash');
        return md5(sha1($password) . $salt);
    }

    /**
     * @param $uid
     * @param string $type
     * @return string
     * @author: zhangkaiyue
     * Time: 2021/4/26   17:34
     */
    protected function getToken($uid, $type = 'user')
    {
        $key = config('app.jwt.key');
        $time = \time();
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => $time, // 签发时间
            "nbf" => $time,  // 生效时间
            "exp" => $time + (60 * 60 * 24),
            "data" => [
                "uid"       => $uid,
                "name"      => $uid,
                "type"      => $type,
            ],
        );
        return $this->jwt::encode($payload, $key);
    }
}