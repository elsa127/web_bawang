<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Verifikasi halaman utama SI Bawang Merah tampil dengan benar.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('SI BAWANG MERAH');
        $response->assertSee('Pantau Kondisi Lahan');
        $response->assertSee('Peta Lahan Bawang Merah');
        $response->assertSee('Sukomoro');
        $response->assertSee('Citra Satelit Sentinel-2');
        $response->assertSee('Algoritma XGBoost');
    }

    /**
     * Verifikasi data produktivitas konsisten dari controller ke view.
     */
    public function test_productivity_data_is_consistent(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Nilai produktivitas prediksi XGBoost harus konsisten di seluruh halaman
        $response->assertSee('10.97');

        // Tidak boleh ada nilai lama yang salah
        $response->assertDontSee('>8.2 Ton/Ha<');
    }

    /**
     * Verifikasi metrik model sesuai hasil Colab (model_metrics.json).
     */
    public function test_model_metrics_are_accurate(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // MAE dan RMSE sesuai temporal CV dari Colab
        $response->assertSee('0.88');
        $response->assertSee('1.16');
        $response->assertSee('MAPE');

        // R² yang benar (0.23), bukan nilai salah lama (0.91)
        $response->assertSee('0.23');
        $response->assertDontSee('>0.91<');
    }

    /**
     * Verifikasi data controller dikirim ke view dengan benar.
     */
    public function test_controller_passes_correct_data_to_view(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('kpiSummary');
        $response->assertViewHas('parcels');
        $response->assertViewHas('districts');
        $response->assertViewHas('yieldPrediction');
        $response->assertViewHas('featureImportance');
        $response->assertViewHas('cropSensors');
        $response->assertViewHas('weeklyTrends');
        $response->assertViewHas('growthLifecycle');
        $response->assertViewHas('recommendations');
        $response->assertViewHas('selectedDistrict');
        $response->assertViewHas('insights');
        $response->assertViewHas('aboutPillars');
    }

    /**
     * Verifikasi 4 kecamatan riset tampil di halaman.
     */
    public function test_all_four_research_districts_are_displayed(): void
    {
        $response = $this->get('/');

        $response->assertSee('Sukomoro');
        $response->assertSee('Bagor');
        $response->assertSee('Gondang');
        $response->assertSee('Rejoso');
    }

    /**
     * Verifikasi feature importance Sentinel-2 tampil lengkap.
     */
    public function test_feature_importance_bands_are_displayed(): void
    {
        $response = $this->get('/');

        // 8 band Sentinel-2 dari feature_importance.json
        $response->assertSee('B12');
        $response->assertSee('B4');
        $response->assertSee('B11');
        $response->assertSee('B2');
        $response->assertSee('NDWI');
        $response->assertSee('B8');
        $response->assertSee('B3');
        $response->assertSee('NDVI');
    }
}
