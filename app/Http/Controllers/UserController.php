<?php

namespace App\Http\Controllers;

use App\Models\OauthAccessToken;
use App\Models\Otp;
use App\Models\otpCount;
use App\Models\partsSelling;
use App\Models\RequiredParts;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class UserController extends Controller
{
    /**
     * @var User
     */
    protected $userModel;
    /**
     * @var Otp
     */
    protected $otpModel;

    /**
     * Instantiate a new ProductController instance.
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->otpModel = new Otp();
    }

    /**
     * Get Users List
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsers(Request $request): JsonResponse
    {
        $data = $this->userModel->userList($request);
        if ($data['total'] == 0) {
            return response()->json(['message' => 'No Users Found', 'status' => false]);
        }
        return response()->json($data);
    }


    /**
     * account self delete
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user) {
            $userId = $user->getAuthIdentifier();
            partsSelling::where('user_id', $userId)->delete();
            RequiredParts::where('user_id', $userId)->delete();
            $userDelete = (new User)->where('id', $userId)->delete();
            if ($userDelete) {
                $delete = OauthAccessToken::where('user_id', $userId)->delete();
                if ($delete) {
                    $response = array(
                        'status' => true,
                        'message' => 'Your Account is delete successfully'
                    );
                }
            } else {
                $response = array(
                    'status' => true,
                    'message' => 'Your Account is already deleted'
                );
            }
        }
        return response()->json($response);
    }


    /**
     * deactivate and reactivate account
     * @param Request $request
     * @return JsonResponse
     */
    public function activityUser(Request $request): JsonResponse
    {
//        $user = auth()->user();
        $mobile = $request->mobile;
        $action = $request->action;
//        $userId = (string)$user->getAuthIdentifier();
        if ($mobile) {
            $userData = (new User)->where('mobile', $mobile)->first()->toArray();
//            dd($userData);
            if ($action === '0') {
                $result = (new User)->where('id', $userData['id'])->update(['activate' => '0']);
                if ($result) {
                    $response = array(
                        'status' => true,
                        'message' => 'Your Account is reactivated successfully'
                    );
                }
            } elseif ($action === '1') {
                $result = (new User)->where('id', $userData['id'])->update(['activate' => '1']);
                if ($result) {
                    $delete = OauthAccessToken::where('user_id', $userData['id'])->delete();
                    if ($delete) {
                        $response = array(
                            'status' => true,
                            'message' => 'Your Account is deactivated successfully'
                        );
                    } else {
                        $response = array(
                            'status' => true,
                            'message' => 'Your Account is already deactivate'
                        );
                    }

                }
            }

        } else {
            $response = array(
                'status' => false,
                'message' => 'Enter required details'
            );
        }
        return response()->json($response);
    }


    /**
     * Distributor Block And Unblock
     * @param Request $request
     * @return JsonResponse
     */
    public function changeUserStatus(Request $request): JsonResponse
    {
        $act = ['is_blocked' => $request->status ? 'true' : 'false'];
        $updateArr = User::where('id', $request->get('id'))->update($act);
        if ($updateArr) {
            $response = array(
                'status' => true,
                'message' => 'User has been ' . ($request->status ? 'Block' : 'Unblock')
            );
            if ($request->status === true) {
                OauthAccessToken::where('user_id', $request->id)->delete();
            }
        } else {
            $response = array(
                'status' => false,
                'message' => 'not affected',
            );
        }

        return response()->json($response);
    }


//     public function login(Request $request): JsonResponse
//     {
//         if ($request->mobile) {
//             $user = User::where('mobile', '=', $request->mobile)->first();
//             if ($user) {
//                 $block = (new User)->where('mobile', '=', $request->mobile)->where('is_blocked', '=', 'true')->first();
//                 if (!$block) {
//                     $otp = 987654;
// //                                    $otp = rand(100000, 999999);

//                     $otp1 = $this->otpModel->createOTP(['mobile' => $request->get('mobile'), 'otp' => $otp]);
//                     if ($otp1) {
// //                        send_sms($request->get('mobile'), $otp, env('LOGIN_FLOW_ID'));
//                         return response()->json([
//                             'message' => 'OTP has been sent to given mobile number',
//                             'status' => true,
//                         ]);
//                     } else {
//                         return response()->json([
//                             'status' => false,
//                             'message' => 'OTP not sent'
//                         ]);
//                     }
//                 } else {
//                     return response()->json(['message' => 'You Are Blocked By Old Automotive Parts', 'status' => false]);
//                 }
//             } else {
//                 $status = $this->userModel->createUser(['mobile' => $request->get('mobile'), 'role' => 'user']);
//                 if ($status) {
//                     $otp = 987654;
//                     $otp = $this->otpModel->createOTP(['mobile' => $request->get('mobile'), 'otp' => $otp]);
//                     // send_sms($request->get('mobile'), $otp, env('LOGIN_FLOW_ID'));
//                     if ($otp) {
//                         return response()->json([
//                             'message' => 'OTP has been sent to given mobile number',
//                             'status' => true,
//                         ]);
//                     } else {
//                         return response()->json([
//                             'status' => false,
//                             'message' => 'OTP not sent'
//                         ]);
//                     }
//                 }
//             }
//         } else {
//             return response()->json(['message' => 'Please Enter Mobile Number', 'status' => false]);
//         }
//         exit();
//     }


    public function login(Request $request): JsonResponse
    {
        if ($request->mobile) {
            $user = User::where('mobile', '=', $request->mobile)->first();
            if ($user) {
                $block = (new User)->where('mobile', '=', $request->mobile)->where('is_blocked', '=', 'true')->first();
                if (!$block) {
                    $deactivated = (new User)->where('mobile', '=', $request->mobile)->where('activate', '=', '1')->first();
                    if (!$deactivated) {

                        if ($request->mobile === '8080808080') {
                            $otp = 987654;
                        } else {
                            $otp = rand(100000, 999999);
                        }
                        $otp1 = $this->otpModel->createOTP(['mobile' => $request->get('mobile'), 'otp' => $otp]);
                        if ($otp1) {
                            send_sms($request->get('mobile'), $otp, env('LOGIN_FLOW_ID'));
                            $otpCount = otpCount::where('id', 1)->first()->toArray();
                            if ($otpCount) {
                                $otpCount['count'] += 1;
                                otpCount::where('id', 1)->update(['count' => $otpCount['count']]);
                            }

                            return response()->json([
                                'message' => 'OTP has been sent to given mobile number',
                                'status' => true,
                            ]);
                        } else {
                            return response()->json([
                                'status' => false,
                                'message' => 'OTP not sent'
                            ]);
                        }
                    } else {
                        return response()->json(['message' => 'Your account is currently deactivate, please activate it first',
                            'status' => true, 'account' => "deactive"]);
                    }
                } else {
                    return response()->json(['message' => 'You Are Blocked By Old Automotive Parts', 'status' => false]);
                }
            } else {
                $status = $this->userModel->createUser(['mobile' => $request->get('mobile'), 'role' => 'user']);
                if ($status) {
                    if ($request->mobile === '8080808080') {
                        $otp = 987654;
                    } else {
                        $otp = rand(100000, 999999);
                    }
                    //$otp = 987654;
//                    $otp = rand(100000, 999999);
                    $otp1 = $this->otpModel->createOTP(['mobile' => $request->get('mobile'), 'otp' => $otp]);
                    send_sms($request->get('mobile'), $otp, env('LOGIN_FLOW_ID'));
                    if ($otp1) {
                         $otpCount = otpCount::where('id', 1)->first()->toArray();
                            if ($otpCount) {
                                $otpCount['count'] += 1;
                                otpCount::where('id', 1)->update(['count' => $otpCount['count']]);
                            }
                        return response()->json([
                            'message' => 'OTP has been sent to given mobile number',
                            'status' => true,
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'OTP not sent'
                        ]);
                    }
                }
            }
        } else {
            return response()->json(['message' => 'Please Enter Mobile Number', 'status' => false]);
        }
        exit();
    }

    public function verifyOtpUser(Request $request)
    {
        $user = new User();

        $getUser = Otp::where('mobile', '=', $request->mobile)->where('otp', '=', $request->otp)->first();

        if ($getUser) {
            Otp::where('mobile', $request->mobile)->delete();
            $user = User::where('mobile', $request->mobile)->first();
            $token = $user->createToken('login')->accessToken;
            if ($user->name === '' || $user->name === null || $user->email === '' || $user->email === null || $user->shopName === '' || $user->shopName === null
                || $user->address === '' || $user->address === null || $user->state === '' || $user->state === null || $user->city === '' || $user->city === null
                || $user->area === '' || $user->area === null || $user->landmark === '' || $user->landmark === null || $user->zipcode === '' || $user->zipcode === null) {
                $updateProfile = false;
            } else {
                $updateProfile = true;
            }
            /*if ($request->has('token')) {
             User::where('id', $user->id)->update(array('push_token' => $request->token));
         }*/
            return response()->json([
                'message' => 'Logged in successfully',
                'updateProfile' => $updateProfile,
                'data' => $user,
                'token' => $token,

                'status' => true
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'not matched'

            ]);

        }

    }

    /**
     * Send OTP for Login
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function loginOTP(Request $request): JsonResponse
    {
        if ($request->get('mobile') != '' || $request->get('mobile') != null) {
            $role = 'user';
            if ($request->has('role')) {
                $role = $request->get('role');
            }
            $password = '';
            if ($request->has('password')) {
                $password = $request->get('password');
            }
            $check = $this->userModel->getUser($request->get('mobile'), $role, $password);
            if (!$check) {
                if ($role === 'admin') {
                    $response = array(
                        'status' => false,
                        'message' => 'Invalid Credentials'
                    );
                    return response()->json($response);
                }
                if ($request->get('role') === "user") {
                    $check = $this->userModel->getUser($request->get('mobile'));
                    if (!$check) {
                        $this->userModel->createUser(['mobile' => $request->get('mobile'), 'role' => $role]);
                    } else if ($check->is_blocked == 'true') {
                        $response = [
                            'status' => false,
                            'message' => 'Your account is blocked'
                        ];
                        return response()->json($response);
                    }
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'Invalid Credentials'
                    ];
                    return response()->json($response);
                }
            }
            if ($check && $check->is_blocked == 'true') {
                $response = [
                    'status' => false,
                    'message' => 'Your account is blocked'
                ];
            } else {
                // $otp = 987654;
                $otp = rand(100000, 999999);
                send_sms($request->get('mobile'), $otp, env('LOGIN_FLOW_ID'));
                $otpCount = otpCount::where('id', 1)->first()->toArray();
                if ($otpCount) {
                    $otpCount['count'] += 1;
                    otpCount::where('id', 1)->update(['count' => $otpCount['count']]);
                }
                $otp1 = $this->otpModel->createOTP(['mobile' => $request->get('mobile'), 'otp' => $otp]);
                if ($otp1) {
                    $response = [
                        'status' => true,
                        'message' => 'OTP has been sent to given mobile number'
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'OTP not sent'
                    ];
                }
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'Please Enter Valid Credentials'
            ];
            return response()->json($response);
        }
        return response()->json($response);
    }

    /**
     * Verify OTP and Login
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        if ($request->get('otp') != '' || $request->get('mobile') != null) {
            $check = $this->otpModel->getOTPData($request->get('mobile'), $request->get('otp'));
            if ($check) {
                $this->otpModel->deleteOTP($check->id);
                $role = 'user';
                if ($request->has('role')) {
                    $role = $request->get('role');
                }
                $password = '';
                if ($request->has('password')) {
                    $password = $request->get('password');
                }
                $user = $this->userModel->getUser($request->get('mobile'), $role, $password);
                if ($user) {
                    $token = $user->createToken('login')->accessToken;
                    if ($request->has('token')) {
                        $this->userModel->updateData($user->id, ['push_token' => $request->get('token')]);
                    }
                    return response()->json([
                        'message' => 'Logged in successfully',
                        'data' => $user,
                        'token' => $token,
                        'status' => true
                    ]);
                } else {
                    $response = array(
                        'status' => false,
                        'message' => 'User not found'
                    );
                }
            } else {
                $response = array(
                    'status' => false,
                    'message' => 'Incorrect OTP'
                );
            }
        } else {
            $response = array(
                'status' => false,
                'message' => 'Please enter mobile and OTP'
            );
        }
        return response()->json($response);
    }

    /**
     * Resend OTP
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function resendOTP(Request $request): JsonResponse
    {
        if ($request->get('mobile') != null) {
            return response()->json($this->loginOTP($request)->original);
        } else {
            $response = array(
                'status' => false,
                'message' => 'Please enter mobile number'
            );
        }
        return response()->json($response);
    }


    /**
     * Resend OTP
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function resendUserOTP(Request $request): JsonResponse
    {
        if ($request->get('mobile') != null) {
            return response()->json($this->login($request)->original);
        } else {
            $response = array(
                'status' => false,
                'message' => 'Please enter mobile number'
            );
        }
        return response()->json($response);
    }

    /**
     * Logout User
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $request->user()->token()->revoke();
        }
        return response()->json([
            'message' => 'Successfully logged out',
            'status' => true
        ]);
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $user = User::where('id', $id)->first();
        if ($user) {
            $response = array(
                'status' => true,
                'data' => $user
            );
            $code = 200;
        } else {
            $response = array(
                'status' => false,
                'message' => 'user not found'
            );
            $code = 500;
        }
        return response()->json($response, $code);
    }


    /**
     * @return JsonResponse
     */
    public function adminDetail(): JsonResponse
    {
        $user = Auth::user();
        $user = User::where('id', $user->getAuthIdentifier())->first();
//        dd($user);
        if ($user) {
            $response = array(
                'status' => true,
                'data' => $user
            );
            $code = 200;
        } else {
            $response = array(
                'status' => false,
                'message' => 'user not found'
            );
            $code = 500;
        }
        return response()->json($response, $code);
    }

    /**
     * Change User Password
     * @param Request $request
     * @return JsonResponse
     */
    public function change_password(Request $request): JsonResponse
    {
        $authUser = Auth::user();
        $user = (new User)->find($authUser->getAuthIdentifier());
        // dd($user);
        $check = $this->userModel->getUser($user->mobile, $user->role, $request->get('oldPassword'));

        // $checkPassword = $user->where('password', hash('sha512', $request->get('oldPassword')));
        // dd( $check);
        if ($check) {
            if ($request->password) {
                $user->update(['password' => hash('sha512', $request->get('password'))]);
                return response()->json(['message' => 'Password has been changed successfully', 'status' => true]);
            } else {
                return response()->json(['message' => 'Please enter New password', 'status' => false]);
            }
        } else {
            $response = array(
                'status' => false,
                'message' => 'Old Password does not match'
            );
        }
        return response()->json($response);
    }

    /**
     * Get User Profile
     * @return JsonResponse
     */

    public function get_profile(): JsonResponse
    {

// TODO Urgent Part Sell now in part selling
// TODO Normal Part Sell in Required Part
        $user = Auth::user();
        if ($user) {
//            dd($user->getAuthIdentifier());
            $userData = User::where('id', '=', $user->getAuthIdentifier())->select('id', 'name', 'email', 'mobile', 'address', 'location', 'state', 'city', 'area', 'landmark', 'zipcode', 'shopName')->first()->toArray();
            $urgent = partsSelling::where('is_removed', '=', '0')->where('user_id', '=', $user->getAuthIdentifier())->select('id', 'brand_id', 'brandModel_id', 'version_id', 'cat_id', 'subCat_id', 'modelYear', 'approxRate', 'quantity', 'conditionPart', 'image', 'urgentSell', 'postDate')
                ->with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat')->get()->toArray();
            $normal = RequiredParts::where('is_removed', '=', '0')->where('user_id', '=', $user->getAuthIdentifier())->select('id', 'brand_id', 'brandModel_id', 'version_id', 'cat_id', 'subCat_id', 'modelYear', 'approxRate', 'quantity', 'conditionPart', 'image', 'urgentSell', 'postDate')
                ->with('Brand', 'BrandModel', 'BrandVersion', 'Cat', 'SubCat')->get()->toArray();
            $combine = array_merge($urgent, $normal);

            if ($combine) {
                foreach ($combine as $i => $da) {
                    $combine[$i]['urgentSell'] = (int)$combine[$i]['urgentSell'];
//                    $out[strtotime($combine[$i]['postDate'])] = $combine[$i]['postDate'];
//                    $combine[$i]['postDate'] = sort(strtotime($combine[$i]['postDate']));
//                    unset(
//                        $combine[$i]['is_removed'],
//                        $combine[$i]['is_blocked'],
//                        $combine[$i]['user_id'],
//                        $combine[$i]['brand_id'],
//                        $combine[$i]['brandModel_id'],
//                        $combine[$i]['version_id'],
//                        $combine[$i]['cat_id'],
//                        $combine[$i]['subCat_id']
//                    );
                }
                $response = array(
                    'status' => true,
                    'data' => $userData,
                    'yourProducts' => $combine,
                );
            } else {
                $response = array(
                    'status' => true,
                    'data' => $userData,
                    'yourProducts' => [],
                );
            }
        } else {
            $response = array(
                'status' => false,
                'message' => 'User not found'
            );
        }
        return response()->json($response);
    }


    /**
     * After Combine data  particular one product update
     * @return JsonResponse
     */

    public function updateUserProduct(Request $request): JsonResponse
    {
        // TODO Urgent Part Sell now in part selling
        // TODO Normal Part Sell in Required Part
        $user = Auth::user();
        $data = $request->all();

        if ($user) {
            if ($data['image'] != "") {
                $data['image'] = explode(',', $data['image']);
                foreach ($data['image'] as $d => $da) {
                    if (File::exists(public_path("temp/" . $da))) {
                        File::move(public_path("temp/" . $da), public_path("partsSelling/" . $da));
                        File::delete("temp/" . $da);
                    }
                }
            }

            $data['image'] = implode(',', $data['image']);

            if ($request->urgentSell === 0 || $request->urgentSell === '0') {
                if ($request->has('id') && $request->id !== null) {
                    // $data['image'] = implode(',', $data['image']);
                    $data['postDate'] = date('d-m-Y');
                    $status = RequiredParts::where('id', $request->id)->update($data);
                    $message = 'normal Your part to be sold has been updated successfully';
                    if ($status) {
                        $response = array(
                            'status' => true,
                            'message' => $message,
                        );
                    } else {
                        $response = array(
                            'status' => false,
                            'message' => 'Something went wrong'
                        );
                    }
                }

            } else if ($request->urgentSell === 1 || $request->urgentSell === '1') {
                if ($request->has('id') && $request->id !== null) {
                    $data['postDate'] = date('d-m-Y');
                    $status = partsSelling::where('id', $request->id)->update($data);
                    $message = 'Your part to be sold has been updated successfully';
                    if ($status) {
                        $response = array(
                            'status' => true,
                            'message' => $message,
                        );
                    } else {
                        $response = array(
                            'status' => false,
                            'message' => 'Something went wrong'
                        );
                    }
                }
            } else {
                $response = array(
                    'status' => false,
                    'message' => 'Something went wrong'
                );
            }

        } else {
            $response = array(
                'status' => false,
                'message' => 'User not found'
            );
        }
        return response()->json($response);
    }

    /**
     * Update user profile
     * @param Request $request
     * @return JsonResponse
     */
    public function update_profile(Request $request): JsonResponse
    {
        $data = $request->all();
        $user = auth()->user();
        unset($data['id']);

        $status = (new User)->find($user->getAuthIdentifier())->update($data);
        if ($status) {
            $response = array(
                'status' => true,
                'message' => 'Profile updated successfully',
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'Something went wrong'
            );

        }
        return response()->json($response, 200);
    }

    /**
     * For Forgot Password First step
     * @param Request $request
     * @return JsonResponse
     */
    public function forgot(Request $request): JsonResponse
    {
        if ($request->email) {
            $user = (new User)->where('email', $request->email)->first();
            if ($user) {
                $random = Str::random(8);
                $data = array('link' => $random, 'user' => $user);
//                dd($request->email);
                Mail::send('mail', $data, function ($message) use ($request, $user) {
                    $message->to($request->email, $user->name)
                        ->subject('Reset Password');
                    $message->from(env('MAIL_FROM_ADDRESS'), 'Reset Password');
                });
                (new User)->where('id', $user->id)->update(array('token' => $random));
                $response = array(
                    'status' => true,
                    'message' => 'Reset link has been sent to your email'
                );
            } else {
                $response = array(
                    'status' => false,
                    'message' => 'User not found'
                );
            }
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Please enter email', 'status' => false], 500);
        }
    }

    /**
     *  user second step verify Token
     * @param Request $request
     * @return JsonResponse
     */
    public function tokenVerification(Request $request): JsonResponse
    {
        if ($request->token) {
            $user = (new User)->where('token', $request->token)->first();
        } else {
            $user = request()->user();
        }
        if ($user) {
            $response = array(
                'status' => true,
                'message' => 'Your Token Match Successfully'
            );
        } else {
            $response = array(
                'status' => false,
                'message' => 'You Have Entered Wrong Token'
            );
        }
        return response()->json($response);
    }

    /**
     * Third step user reset password
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        if ($request->token) {
            $user = (new User)->where('token', $request->token)->first();
        } else {
            $user = request()->user();
        }
        if ($user) {
            if ($request->password) {
                $update = (new User)->where('id', $user->id)->update(['password' => hash('sha512', $request->password),
                    'token' => null]);
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Please Enter Password'
                ];
                return response()->json($response);
            }
            if ($update) {
                $status = true;
                $message = 'Password has been changed successfully';
            } else {
                $status = false;
                $message = 'You Have Entered Wrong Password';
            }
        }
        return response()->json(['status' => $status, 'message' => $message]);
    }
}
