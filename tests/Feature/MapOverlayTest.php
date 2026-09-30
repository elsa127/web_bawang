<?php

namespace Tests\Feature;

use Tests\TestCase;

class MapOverlayTest extends TestCase
{
    public function test_map_reports_missing_gondang_coverage_separately_from_negative_predictions(): void
    {
        $response = $this->getJson('/map-info');

        $response->assertOk()
            ->assertJsonPath('schema', 2)
            ->assertJsonFragment(['district' => 'Gondang', 'missing_pixels' => 570228, 'missing_percent' => 41.74]);
    }

    public function test_prepared_candidate_png_is_served(): void
    {
        $response = $this->get('/map-overlay/candidate');

        $response->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_dashboard_explains_esri_clouds_and_missing_landcover_filter(): void
    {
        $response = $this->get('/');

        $response->assertSeeText('Basemap satelit berasal dari Esri dan dapat mengandung awan');
        $response->assertDontSee('value="candidate_landcover"', false);
        $response->assertDontSeeText('Umur tanaman dan saran pemupukan belum tersedia');
        $response->assertDontSeeText('Skor ? 0,50');
        $response->assertSeeText('Prediksi hasil bawang merah');
    }

    public function test_satellite_without_a_prepared_export_returns_503(): void
    {
        $this->getJson('/map-overlay/satellite')->assertStatus(503);
    }

    public function test_upload_timestamp_changes_do_not_invalidate_map_content(): void
    {
        $path = base_path('ml_model/data/OFFICIAL_DATA_2025/Batas_4_Kecamatan_Nganjuk.geojson');
        $originalTime = filemtime($path);
        try {
            touch($path, $originalTime + 120);
            clearstatcache(true, $path);
            $this->getJson('/map-info')->assertOk();
        } finally {
            touch($path, $originalTime);
            clearstatcache(true, $path);
        }
    }

    public function test_debug_file_paths_are_not_public(): void
    {
        $this->get('/debug-tif/probability')->assertNotFound();
    }

    public function test_unknown_layer_returns_404(): void
    {
        $this->get('/map-overlay/not-a-layer')->assertNotFound();
    }

    public function test_unprepared_map_returns_503_without_running_a_generator(): void
    {
        $originalStorage = $this->app->storagePath();
        $this->app->useStoragePath(sys_get_temp_dir().'/bawang-missing-'.bin2hex(random_bytes(8)));

        try {
            $this->getJson('/map-info')->assertStatus(503);
        } finally {
            $this->app->useStoragePath($originalStorage);
        }
    }
}
