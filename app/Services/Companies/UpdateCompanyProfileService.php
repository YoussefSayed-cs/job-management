<?php

namespace App\Services\Companies;

use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UpdateCompanyProfileService
{
    public function update(Company $company, array $data): Company
    {
        $logo = $data['logo'] ?? null;

        unset($data['logo']);

        // Only allow directly editable company fields.
        $attributes = [];

        foreach (['description', 'location', 'website'] as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field] = $data[$field];
            }
        }

        $oldLogo = $company->logo;
        $newLogoPath = null;

        // Upload the new logo before updating the database.
        if ($logo instanceof UploadedFile) {
            $newLogoPath = Storage::disk('public')
                ->putFile('company-logos', $logo);

            if (! $newLogoPath) {
                throw new RuntimeException('Unable to upload company logo.');
            }

            $attributes['logo'] = $newLogoPath;
        }

        try {
            DB::transaction(function () use ($company, $attributes) {
                $company->update($attributes);
            });
        } catch (Throwable $exception) {
            // Remove the newly uploaded file if database update fails.
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        // Delete the old logo only after the database update succeeds.
        if ($newLogoPath && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        return $company->refresh();
    }
}
