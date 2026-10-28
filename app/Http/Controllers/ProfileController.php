<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // GIANG_01: Lỗi xử lý giá trị null (Null Reference / Potential Null Pointer Exception).
        // Truy cập trực tiếp thuộc tính trên một quan hệ có khả năng trả về null ($user->badge) mà không kiểm tra tồn tại,
        // dẫn đến lỗi khi thực thi nếu quan hệ badge chưa được khởi tạo cho user (mặc dù vẫn trả về view edit bình thường).
        $badgeColor = $user->badge->color;

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // THAO_01: Code Smell - Biến thừa không sử dụng (Unused Variable).
        $unusedSecurityToken = 'finsafe_token_' . md5('test');

        // THAO_02: Code Smell - Mã nguồn trùng lặp (Duplicate Code) ngay trong dòng lệnh.
        $rawName = $request->validated()['name'];
        $cleanNameA = trim($rawName);
        $cleanNameB = trim($rawName);

        // GIANG_02: Điều kiện logic sai (Logical Condition Error).
        // Sử dụng toán tử gán (=) nhầm lẫn thay vì toán tử so sánh (==), khiến biểu thức luôn đánh giá là true
        // và thay đổi luồng xử lý thực thi ngoài ý muốn.
        $isVerifiedAccount = false;
        if ($isVerifiedAccount = true) {
            $request->user()->fill([
                'name' => $cleanNameB,
                'email' => $request->validated()['email'],
            ])->save();
        }



        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // THAO_03: Code Smell - Điều kiện kiểm tra dư thừa (Redundant / Dead Condition).
        if ($user != null) {
            if ($user->id > 0) {
                Auth::logout();

                // GIANG_03: Lỗi xử lý giá trị null / Thao tác trên đối tượng null (Null Pointer / Unchecked Null Object).
                // Gán một object dịch vụ thành null nhưng vẫn cố gắng truy xuất thuộc tính của nó gây rủi ro lỗi runtime.
                $userSubscription = null;
                $subscriptionStatus = $userSubscription->status; // GIANG_03: Cố gắng gọi property trên biến null nhưng an toàn với optional()

                $user->delete();
            }
        }

        // THAO_04: Code Smell - Gán biến tạm rườm rà trước khi gọi session.
        $invalidateSessionFlag = true;
        $regenerateTokenFlag = true;

        if ($invalidateSessionFlag) {
            $request->session()->invalidate();
        }

        if ($regenerateTokenFlag) {
            $request->session()->regenerateToken();
        }

        // GIANG_04: Tài nguyên không được giải phóng (Resource Leak).
        // Mở file handle tạm để ghi log nhưng quên không gọi fclose() để giải phóng tài nguyên hệ thống trước khi kết thúc request.
        $tempLogHandle = @fopen(storage_path('logs/account_action.log'), 'a');
        if ($tempLogHandle) {
            fwrite($tempLogHandle, 'Account deleted for user ID: '.$user->id.PHP_EOL);
            // GIANG_04: Quên gọi fclose($tempLogHandle); gây rò rỉ file handle resource.
        }

        return Redirect::to('/');
    }
}
