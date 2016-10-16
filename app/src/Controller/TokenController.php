<?php
namespace App\Controller;

use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\JsonApi\Document\Error;

use Firebase\JWT\JWT;
use App\Model\User;

final class TokenController extends BaseController
{
    protected static function createToken($request, $tokenExpire)
    {
        $tokenExpire = isset($tokenExpire) ? $tokenExpire : 3600;

        $secret_key = getenv('SECRET_KEY');
        $token = array(
            'iss' => getenv('AUTH_ISS'),
            'aud' => $request->getUri()->getHost(),
            'iat' => time(),
            'exp' => time() + $tokenExpire,
        );
        $jwt = JWT::encode($token, $secret_key);
        return $jwt;
    }

    public static function validateToken($token, $whiteList = [])
    {
        try {
            $payload = JWT::decode($token, getenv('SECRET_KEY'), ['HS256']);
            if (!in_array($payload->aud, $whiteList)) {
                return false;
            }
            return true;
        } catch (\Exception $e){
            return false;
        }
    }

    /**
     * @api {post} /token Получение токена
     * @apiName CreateToken
     * @apiGroup Token
     *
     * @apiDescription Метод для получения авторизационного токена. Он отправляется в заголовке запроса:
     *
     * Authorization: Bearer xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
     *
     * @apiParam {String} username Логин
     * @apiParam {String} password Пароль
     *
     * @apiParamExample {json} Пример запроса:
     *    {
     *      "data":{
     *        "attributes":{
     *          "username":"admin@example.com",
     *          "password": "qwerty"
     *        }
     *      }
     *    }
     *
     * @apiSuccessExample {json} Успешно (200)
     *     HTTP/1.1 200 OK
     *     {
     *       "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOmZhbHNlLCJhdWQiOiJza2VsZXRvbi5kZXYiLCJpYXQiOjE0NzY0Mjk4NjksImV4cCI6MTQ3NjQzMzQ2OX0.NJn_-lK28kEZyZqygLr6B-FZ2zC2-1unStayTGicP5g",
     *       "user": {
     *         "id": 1,
     *         "email": "mail@example.com",
     *         "full_name": "Тестовый пользоатель",
     *         "role_id": "1",
     *         "created_by": 0,
     *         "updated_by": null,
     *         "created_at": "2016-07-24 14:07:54",
     *         "updated_at": "2016-10-14 10:24:29",
     *         "deleted_at": null,
     *         "status": 1
     *       }
     *     }
     *
     * @apiUse StandardErrors
     */
    public function auth($request, $response, $args)
    {
        $params = $request->getParsedBody();

        if(!isset($params['data']['attributes'])){
            $error = new Error(
                'token',
                null,
                '400',
                '400',
                'Invalid Attribute',
                'Not required attributes - data.'
            );

            $result = Encoder::instance()->encodeError($error);

            return $this->renderer->jsonApiRender($response, 400, $result);
        }

        $params = $params['data']['attributes'];

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        $validator = $this->validation->make($params, $rules);

        if ($validator->fails()) {
            $messages = implode(' ', $validator->messages()->all());

            $error = new Error(
                'token',
                null,
                '400',
                '400',
                'Invalid Attribute',
                $messages
            );

            $result = Encoder::instance()->encodeError($error);

            return $this->renderer->jsonApiRender($response, 400, $result);
        }

        $user = User::findUserByEmail($params['username']);

        if ($user && password_verify($params['password'], $user->password)) {
            $token = self::createToken($request, $this->settings['params']['tokenExpire']);
            $user->access_token = md5($token);
            $user->save();
        } else {
            $error = new Error(
                'token',
                null,
                '400',
                '400',
                'Invalid Attribute',
                'Invalid password or username'
            );

            $result = Encoder::instance()->encodeError($error);

            return $this->renderer->jsonApiRender($response, 400, $result);
        };

        $result = [
            'access_token' => $token,
            'user' => $user->toArray()
        ];

        return $this->renderer->jsonApiRender($response, 200, json_encode($result));
    }
}