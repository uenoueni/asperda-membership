<?php

namespace App\Services;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MemberService
{
    public function completeProfile(User $user, array $data, ?UploadedFile $document = null): Member
    {
        $member = $user->member;

        $documentPath = null;
        if ($document) {
            $documentPath = Storage::disk('public')->putFile('documents', $document);
        }

        $user->update([
            'bank_name'         => $data['bank_name'],
            'bank_account_no'   => $data['bank_account_no'],
            'bank_account_name' => $data['bank_account_name'],
        ]);

        $member->primaryBranch()->delete();

        $member->branches()->create([
            'branch_name'   => $data['branch_name'],
            'province_code' => $data['province_code'],
            'city_code'     => $data['city_code'],
            'district_code' => $data['district_code'] ?? null,
            'address'       => $data['address'],
            'unit_count'    => $data['unit_count'],
            'is_primary'    => true,
            'document_path' => $documentPath,
        ]);

        return $member->fresh(['primaryBranch', 'payments', 'certificates']);
    }

    public function update(Member $member, array $data): Member
    {
        $userFields = array_intersect_key($data, array_flip([
            'name', 'phone', 'bank_name', 'bank_account_no', 'bank_account_name',
        ]));

        if (!empty($userFields)) {
            $member->user->update($userFields);
        }

        return $member->fresh(['user', 'primaryBranch', 'payments', 'certificates']);
    }
}
