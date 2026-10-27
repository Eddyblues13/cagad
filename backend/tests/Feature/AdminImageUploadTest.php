<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_images_are_uploaded_and_returned_as_optimised_urls(): void
    {
        Http::fake([
            'api.cloudinary.com/*/image/upload' => Http::response([
                'public_id' => 'cagad/blog/content/chart',
                'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/v1/cagad/blog/content/chart.png',
                'width' => 3000,
                'height' => 2000,
            ]),
        ]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->postJson('/admin/uploads/images', ['file' => UploadedFile::fake()->image('chart.png')])
            ->assertOk()
            ->assertExactJson([
                'location' => 'https://res.cloudinary.com/test-cloud/image/upload/c_limit,w_1600,f_auto,q_auto/v1/cagad/blog/content/chart.png',
            ]);

        Http::assertSent(fn (Request $request): bool => collect($request->data())->firstWhere('name', 'folder')['contents'] === 'cagad/blog/content');
    }

    public function test_only_images_are_accepted(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->postJson('/admin/uploads/images', ['file' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        Http::assertNothingSent();
    }

    public function test_cloudinary_errors_are_passed_back_to_the_editor(): void
    {
        Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'File size too large']], 400)]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->postJson('/admin/uploads/images', ['file' => UploadedFile::fake()->image('huge.jpg')])
            ->assertStatus(502)
            ->assertJson(['message' => 'File size too large']);
    }

    public function test_guests_cannot_upload(): void
    {
        $this->postJson('/admin/uploads/images', ['file' => UploadedFile::fake()->image('x.jpg')])
            ->assertUnauthorized();
    }
}
