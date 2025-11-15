<?php

namespace Tests\Feature;

use App\Models\Questionnaire;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSection;
use App\Models\QuestionnaireVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WorkFunction;
use App\Support\Hash;
use Tests\TestCase;

class AssessmentFlowTest extends TestCase
{
    public function testStaffUserCompletesAssessment(): void
    {
        $tenant = Tenant::create([
            'name' => 'Demo Tenant',
            'slug' => 'demo',
            'primary_contact_email' => 'contact@example.com',
            'is_active' => true,
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Jane Staff',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $workFunction = WorkFunction::create([
            'tenant_id' => $tenant->id,
            'code' => 'HRM',
            'name' => 'HR Management',
        ]);

        $questionnaire = Questionnaire::create([
            'tenant_id' => $tenant->id,
            'name' => 'Baseline Survey',
        ]);

        $version = QuestionnaireVersion::create([
            'questionnaire_id' => $questionnaire->id,
            'version_number' => 1,
            'status' => 'published',
        ]);

        $section = QuestionnaireSection::create([
            'questionnaire_version_id' => $version->id,
            'title' => 'Section A',
            'order_index' => 1,
        ]);

        $item = QuestionnaireItem::create([
            'questionnaire_version_id' => $version->id,
            'section_id' => $section->id,
            'type' => 'likert',
            'code' => 'Q1',
            'text' => 'Rate your skills',
            'is_required' => true,
            'order_index' => 1,
        ]);

        $login = $this->withHeaders(['X-Tenant' => $tenant->slug])->postJson('/api/v1/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        $login->assertOk();
        $token = $login->json('token');

        $assessmentResponse = $this->withHeaders([
            'X-Tenant' => $tenant->slug,
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/my/assessments', [
            'work_function_id' => $workFunction->id,
            'questionnaire_version_id' => $version->id,
            'performance_period' => '2024 Q1',
        ]);

        $assessmentResponse->assertOk();
        $assessmentId = $assessmentResponse->json('id');

        $submitResponse = $this->withHeaders([
            'X-Tenant' => $tenant->slug,
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/v1/my/assessments/' . $assessmentId . '/submit', [
            'responses' => [
                [
                    'questionnaire_item_id' => $item->id,
                    'numeric_value' => 5,
                ],
            ],
        ]);

        $submitResponse->assertOk();
        $submitResponse->assertJsonPath('status', 'submitted');
        $submitResponse->assertJsonPath('score_percent', 100.0);
    }
}
