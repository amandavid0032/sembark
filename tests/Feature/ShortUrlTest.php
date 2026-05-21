<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Mail;

class ShortUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_member_can_create_short_urls()
    {
        $company = Company::create(['name' => 'Test Company']);

        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company->id]);
        $this->actingAs($admin)
            ->post('/short-urls', ['original_url' => 'https://admin.example.com'])
            ->assertRedirect();
        $this->assertDatabaseHas('short_urls', [
            'original_url' => 'https://admin.example.com',
            'user_id' => $admin->id,
            'company_id' => $company->id,
        ]);

        $member = User::factory()->create(['role' => 'Member', 'company_id' => $company->id]);
        $this->actingAs($member)
            ->post('/short-urls', ['original_url' => 'https://member.example.com'])
            ->assertRedirect();
        $this->assertDatabaseHas('short_urls', [
            'original_url' => 'https://member.example.com',
            'user_id' => $member->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_superadmin_cannot_create_short_urls()
    {
        $user = User::factory()->create(['role' => 'SuperAdmin', 'company_id' => null]);
        $response = $this->actingAs($user)->post('/short-urls', [
            'original_url' => 'https://example.com',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('short_urls', ['original_url' => 'https://example.com']);
    }

    public function test_superadmin_can_see_all_short_urls_across_companies()
    {
        $company1 = Company::create(['name' => 'Company 1']);
        $company2 = Company::create(['name' => 'Company 2']);
        $superadmin = User::factory()->create(['role' => 'SuperAdmin', 'company_id' => null]);
        $member1 = User::factory()->create(['role' => 'Member', 'company_id' => $company1->id]);
        $member2 = User::factory()->create(['role' => 'Member', 'company_id' => $company2->id]);

        $url1 = ShortUrl::create(['original_url' => 'http://1', 'short_code' => 'c1', 'company_id' => $company1->id, 'user_id' => $member1->id]);
        $url2 = ShortUrl::create(['original_url' => 'http://2', 'short_code' => 'c2', 'company_id' => $company2->id, 'user_id' => $member2->id]);

        $response = $this->actingAs($superadmin)->get('/short-urls');
        $response->assertStatus(200);
        $response->assertViewHas('urls', function ($urls) use ($url1, $url2) {
            $ids = $urls->pluck('id')->all();
            return count($ids) === 2 && in_array($url1->id, $ids) && in_array($url2->id, $ids);
        });
    }

    public function test_admin_can_only_see_short_urls_created_in_their_own_company()
    {
        $company1 = Company::create(['name' => 'Company 1']);
        $company2 = Company::create(['name' => 'Company 2']);
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company1->id]);
        $member1 = User::factory()->create(['role' => 'Member', 'company_id' => $company1->id]);
        $member2 = User::factory()->create(['role' => 'Member', 'company_id' => $company2->id]);

        $own = ShortUrl::create(['original_url' => 'http://own', 'short_code' => 'own', 'company_id' => $company1->id, 'user_id' => $member1->id]);
        ShortUrl::create(['original_url' => 'http://other', 'short_code' => 'oth', 'company_id' => $company2->id, 'user_id' => $member2->id]);

        $response = $this->actingAs($admin)->get('/short-urls');
        $response->assertStatus(200);
        $response->assertViewHas('urls', function ($urls) use ($own) {
            return $urls->count() === 1 && $urls->first()->id === $own->id;
        });
    }

    public function test_member_can_only_see_short_urls_created_by_themselves()
    {
        $company = Company::create(['name' => 'Company']);
        $member = User::factory()->create(['role' => 'Member', 'company_id' => $company->id]);
        $other = User::factory()->create(['role' => 'Member', 'company_id' => $company->id]);

        $mine = ShortUrl::create(['original_url' => 'http://mine', 'short_code' => 'mine', 'company_id' => $company->id, 'user_id' => $member->id]);
        ShortUrl::create(['original_url' => 'http://theirs', 'short_code' => 'thrs', 'company_id' => $company->id, 'user_id' => $other->id]);

        $response = $this->actingAs($member)->get('/short-urls');
        $response->assertStatus(200);
        $response->assertViewHas('urls', function ($urls) use ($mine) {
            return $urls->count() === 1 && $urls->first()->id === $mine->id;
        });
    }

    public function test_short_urls_are_publicly_resolvable_and_redirect_to_the_original_url()
    {
        $company = Company::create(['name' => 'Test Company']);
        $user = User::factory()->create(['role' => 'Member', 'company_id' => $company->id]);
        ShortUrl::create(['original_url' => 'https://example.com', 'short_code' => 'xyz', 'company_id' => $company->id, 'user_id' => $user->id]);

        $this->get('/s/xyz')->assertRedirect('https://example.com');
    }

    public function test_superadmin_can_invite_admin_into_existing_company()
    {
        Mail::fake();

        $superadmin = User::factory()->create(['role' => 'SuperAdmin', 'company_id' => null]);
        $company = Company::create(['name' => 'Brand New Co']);

        $response = $this->actingAs($superadmin)->post('/invitations', [
            'email' => 'newadmin@example.com',
            'role' => 'Admin',
            'company_id' => $company->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('invitations', [
            'email' => 'newadmin@example.com',
            'role' => 'Admin',
            'company_id' => $company->id,
        ]);
    }

    public function test_superadmin_can_manage_companies()
    {
        $superadmin = User::factory()->create(['role' => 'SuperAdmin', 'company_id' => null]);

        $this->actingAs($superadmin)
            ->post('/companies', ['name' => 'New Co'])
            ->assertRedirect();
        $this->assertDatabaseHas('companies', ['name' => 'New Co']);

        $company = Company::where('name', 'New Co')->firstOrFail();

        $this->actingAs($superadmin)
            ->put('/companies/' . $company->id, ['name' => 'Renamed Co'])
            ->assertRedirect();
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Renamed Co']);

        $this->actingAs($superadmin)
            ->delete('/companies/' . $company->id)
            ->assertRedirect();
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    public function test_non_superadmin_cannot_manage_companies()
    {
        $company = Company::create(['name' => 'Co']);
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company->id]);

        $this->actingAs($admin)->get('/companies')->assertForbidden();
        $this->actingAs($admin)->post('/companies', ['name' => 'X'])->assertForbidden();
    }

    public function test_admin_cannot_invite_another_admin()
    {
        $company = Company::create(['name' => 'Co']);
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company->id]);

        $response = $this->actingAs($admin)->post('/invitations', [
            'email' => 'second@example.com',
            'role' => 'Admin',
        ]);
        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('invitations', ['email' => 'second@example.com']);
    }

    public function test_admin_can_invite_member_in_own_company()
    {
        $company = Company::create(['name' => 'Co']);
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company->id]);

        $response = $this->actingAs($admin)->post('/invitations', [
            'email' => 'newmember@example.com',
            'role' => 'Member',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('invitations', [
            'email' => 'newmember@example.com',
            'role' => 'Member',
            'company_id' => $company->id,
        ]);
    }

    public function test_invited_user_can_accept_and_log_in()
    {
        Mail::fake();

        $company = Company::create(['name' => 'Co']);
        $admin = User::factory()->create(['role' => 'Admin', 'company_id' => $company->id]);

        $this->actingAs($admin)->post('/invitations', [
            'email' => 'aman@plaxonic.com',
            'role' => 'Member',
        ])->assertRedirect();

        Mail::assertSent(InvitationMail::class, fn ($mail) => $mail->hasTo('aman@plaxonic.com'));

        $invitation = \App\Models\Invitation::where('email', 'aman@plaxonic.com')->firstOrFail();

        $this->post('/logout');

        $this->get('/invitations/accept/' . $invitation->token)->assertStatus(200);

        $response = $this->post('/invitations/accept/' . $invitation->token, [
            'name' => 'Aman',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'aman@plaxonic.com',
            'role' => 'Member',
            'company_id' => $company->id,
        ]);

        // Invitation is kept as accepted history (not deleted).
        $acceptedUser = \App\Models\User::where('email', 'aman@plaxonic.com')->firstOrFail();
        $this->assertDatabaseHas('invitations', [
            'email' => 'aman@plaxonic.com',
            'accepted_user_id' => $acceptedUser->id,
        ]);
        $accepted = \App\Models\Invitation::where('email', 'aman@plaxonic.com')->firstOrFail();
        $this->assertNotNull($accepted->accepted_at);

        $this->assertAuthenticated();
    }
}
