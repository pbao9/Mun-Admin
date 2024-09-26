<?php

namespace App\Api\V1\Http\Controllers\Auth;

use App\Admin\Http\Controllers\Controller;
use App\Api\V1\Http\Requests\Auth\{RegisterRequest, LoginRequest, UpdateRequest, UpdatePasswordRequest};
use App\Api\V1\Repositories\User\UserRepositoryInterface;
use App\Api\V1\Services\Auth\AuthServiceInterface;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Request;
use App\Api\V1\Http\Resources\Auth\AuthResource;

/**
 * @group Người dùng
 */

class AuthController extends Controller
{
    //
    private $login;
    public function __construct(
        UserRepositoryInterface $repository,
        AuthServiceInterface $service
    )
    {
        $this->repository = $repository;
        $this->service = $service;
    }
    /**
     * Lấy thông tin user
     * 
     *
     * Lấy user hiện tại thông qua access_token. Trong đó có: 
     * <ul>
     * <li><strong>gender</strong>: 
     *      <ul>
     *          <li>1: Nam</li>
     *          <li>2: Nữ</li>
     *          <li>3: Khác</li>
     *      </ul>
     * </li>
     * 
     * <li><strong>vip</strong>: 
     *      <ul>
     *          <li>1: bình thường</li>
     *          <li>2: Đồng</li>
     *          <li>3: Bạc</li>
     *          <li>4: vàng</li>
     *          <li>5: kim cường</li>
     *      </ul>
     * </li>
     * </ul>
     * 
     * 
     * @authenticated Authorization string required 
     * access_token được cấp sau khi đăng nhập. Example: Bearer 1|WhUre3Td7hThZ8sNhivpt7YYSxJBWk17rdndVO8K
     *
     * 
     * @response {
     * "status": 200,
     *      "message": "Thực hiện thành công.",
     *      "data": {
     *           "id": 2,
     *           "username": "0999999999",
     *           "fullname": "Truong",
     *           "email": "truog@gmai1l.com",
     *           "phone": "0999999999",
     *           "address": null,
     *           "gender": 1,
     *           "vip": 1,
     *           "created_at": "2023-03-26T06:41:42.000000Z"
     *       }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        return response()->json([
            'status' => 200,
            'message' => __('Thực hiện thành công.'),
            'data' => new AuthResource($request->user())
        ]);
    }
    /**
     * Đăng ký
     *
     * Tạo mới 1 user.
     *
     * @bodyParam fullname string required
     * Họ và tên của bạn. Example: Nguyen Van A
     *
     * @bodyParam phone string required
     * Số điện thoại của bạn(Đúng định dạng số điện thoại). Example: 0999999999
     * 
     * @bodyParam email string required
     * Email Của bạn. Example: example@gmail.com
     * 
     * @bodyParam password string required
     * Mật khẩu của bạn. Example: 123456
     * 
     * @bodyParam password_confirmation string required
     * Nhập lại mật khẩu của bạn. Example: 123456
     *
     * 
     * @response 200 {
     *      "status": 200,
     *      "message": "Thực hiện thành công."
     * }
     * @response 400 {
     *      "status": 400,
     *      "message": "Thực hiện không thành công."
     * }
     *
     * @param  App\Api\V1\Http\Requests\Auth\RegisterRequest  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request){
        $instance = $this->service->store($request);
        if($instance){
            return response()->json([
                'status' => 200,
                'message' => __('Thực hiện thành công.')
            ]);
        }
        return response()->json([
            'status' => 400,
            'message' => __('Thực hiện không thành công.')
        ], 400);
    }
    /**
     * Đăng nhập
     *
     * Đăng nhập tài khoản.
     *
     * @bodyParam username string required
     * Tên tài khoản là số điện thoại. Example: 0999999999
     *
     * @bodyParam password string required
     * Mật khẩu của bạn. Example: 123456
     * 
     * @response 200 {
     *      "status": 200,
     *      "message": "Đăng nhập thành công.",
     *      "access_token": "1|WhUre3Td7hThZ8sNhivpt7YYSxJBWk17rdndVO8K"
     * }
     * @response 401 {
     *      "status": 401,
     *      "message": "Tài khoản hoặc mật khẩu không đúng."
     * }
     *
     * @param  App\Api\V1\Http\Requests\Auth\LoginRequest  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request){
        $this->login = $request->validated();
        if($this->resolve()){

            $token = $request->user()
            ->createToken(config('custom_api.token_auth'))->plainTextToken;

            return response()->json([
                'status' => 200,
                'message' =>  __('Đăng nhập thành công.'),
                'access_token' => $token
            ], 200);
        }
        return response()->json([
                'status' => 401,
                'message' =>  __('Tài khoản hoặc mật khẩu không đúng.')
            ], 401);
    }
    /**
     * Cập nhật
     *
     * Cập nhật thông tin user hiện tại. Trong đó có:
     * <ul>
     * <li><strong>gender</strong>: 
     *      <ul>
     *          <li>1: Nam</li>
     *          <li>2: Nữ</li>
     *          <li>3: Khác</li>
     *      </ul>
     * </li>
     * </ul>
     *
     * @authenticated Authorization string required 
     * access_token được cấp sau khi đăng nhập. Example: Bearer 1|WhUre3Td7hThZ8sNhivpt7YYSxJBWk17rdndVO8K
     * 
     * @bodyParam fullname string required
     * Họ và tên của bạn. Example: Nguyen Van A
     * 
     * @bodyParam email string required
     * Email Của bạn. Example: example@gmail.com
     * 
     * @bodyParam gender integer required
     * Giới tính Của bạn. Example: 1
     * 
     * @bodyParam address string
     * Địa chỉ hiện tại của bạn. Example: Phạm Văn Đồng, HCM
     * 
     
     * 
     * @response {
     *      "status": 200,
     *      "message": "Thực hiện thành công.",
     *      "data": {
     *          "id": 1,
     *          "username": "0999999999",
     *          "fullname": "Nguyen Van A",
     *          "email": "example@gmail.com",
     *          "phone": "0999999999",
     *          "address": "998/42/15",
     *          "gender": 1,
     *          "vip": 1,
     *          "created_at": "2023-03-16T05:06:44.000000Z"
     *      }
     * }
     *
     * @param  App\Api\V1\Http\Requests\Auth\UpdateRequest  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request){
        $data = $request->validated();
        $user = $request->user();
        $user->update($data);
        return response()->json([
            'status' => 200,
            'message' => __('Thực hiện thành công.'),
            'data' => new AuthResource($user)
        ], 200);
    }
    /**
     * Cập nhật mật khẩu
     *
     * Cập nhật mật khẩu user hiện tại.
     *
     * @bodyParam old_password string required
     * Mật khẩu cũ của bạn. Example: 123
     * 
     * @bodyParam password string required
     * Mật khẩu của bạn. Example: 123456
     * 
     * @bodyParam password_confirmation string required
     * Nhập lại mật khẩu của bạn. Example: 123456
     * 
     * @authenticated Authorization string required 
     * access_token được cấp sau khi đăng nhập. Example: Bearer 1|WhUre3Td7hThZ8sNhivpt7YYSxJBWk17rdndVO8K
     * 
     * @response {
     *      "status": 200,
     *      "message": "Thực hiện thành công."
     * }
     *
     * @param  App\Api\V1\Http\Requests\Auth\UpdatePasswordRequest  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(UpdatePasswordRequest $request){
        $password = bcrypt($request->input('password'));
        $user = $request->user();
        $user->update([
            'password' => $password
        ]);
        return response()->json([
            'status' => 200,
            'message' => __('Thực hiện thành công.'),
        ], 200);
    }


    protected function resolve(){

        return Auth::attempt($this->login) ? true : false;

    }
}
