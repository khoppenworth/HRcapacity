<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallationTest extends TestCase
{
    public function test_status_endpoint_reports_unconfigured_state(): void
    {
        $response = $this->getJson('/api/v1/install/status');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'isConfigured',
                'tenantCount',
                'adminCount',
                'checks' => ['name', 'passed', 'details'],
            ]);
    }

    public function test_install_endpoint_creates_tenant_and_admin(): void
    {
        $response = $this->postJson('/api/v1/install', [
            'company_name' => 'Acme Inc',
            'company_slug' => 'acme',
            'contact_email' => 'contact@acme.test',
            'admin_name' => 'Ada Admin',
            'admin_email' => 'ada@acme.test',
            'admin_password' => 'secret123',
            'admin_password_confirmation' => 'secret123',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Installation completed successfully.',
            ]);

        $this->assertDatabaseHas('tenants', [
            'slug' => 'acme',
            'primary_contact_email' => 'contact@acme.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'ada@acme.test',
            'role' => 'tenant_admin',
        ]);
    }

    public function test_install_endpoint_blocks_when_already_configured(): void
    {
        $this->test_install_endpoint_creates_tenant_and_admin();

        $response = $this->postJson('/api/v1/install', [
            'company_name' => 'Second Co',
            'company_slug' => 'second',
            'contact_email' => 'ops@second.test',
            'admin_name' => 'Sam Second',
            'admin_email' => 'sam@second.test',
            'admin_password' => 'secret123',
            'admin_password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(409);
    }
}
