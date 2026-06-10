<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Member\CompleteProfileRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MemberController extends BaseController
{
    public function __construct(private MemberService $memberService) {}

    public function show(): JsonResponse
    {
        $member = Auth::user()->member()->with([
            'user', 'primaryBranch', 'payments', 'certificates',
        ])->first();

        if (!$member) {
            return $this->error('Data member tidak ditemukan.', 404);
        }

        return $this->success(new MemberResource($member));
    }

    public function update(UpdateMemberRequest $request): JsonResponse
    {
        $member = Auth::user()->member;

        if (!$member) {
            return $this->error('Data member tidak ditemukan.', 404);
        }

        $member = $this->memberService->update($member, $request->validated());

        return $this->success(new MemberResource($member), 'Data berhasil diperbarui.');
    }

    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        $user   = Auth::user();
        $member = $user->member()->with('primaryBranch')->first();

        if (!$member) {
            return $this->error('Akun belum terdaftar sebagai member.', 403);
        }

        $member = $this->memberService->completeProfile(
            $user,
            $request->validated(),
            $request->file('document')
        );

        return $this->success(
            new MemberResource($member),
            'Profil berhasil disimpan.',
            201
        );
    }
}
