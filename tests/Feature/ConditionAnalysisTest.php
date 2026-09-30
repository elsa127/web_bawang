<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConditionAnalysisTest extends TestCase
{
    public function test_analysis_uses_exported_indices_and_reflectance_for_sukomoro(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('conditionAnalysis', function (array $analysis): bool {
            $this->assertSame(0.498158, round($analysis['current']['NDVI'], 6));
            $this->assertSame(-0.472325, round($analysis['current']['NDWI'], 6));
            $this->assertSame(0.118764, round($analysis['current']['B12'], 6));
            $this->assertSame([2023, 2024, 2025], array_column($analysis['annual'], 'year'));
            $this->assertSame([0.444, 0.490, 0.498], array_map(fn (array $row): float => round($row['ndvi'], 3), $analysis['annual']));
            $this->assertSame(['Bagor', 'Gondang', 'Rejoso', 'Sukomoro'], array_column($analysis['districts'], 'district'));
            $this->assertSame([0.528, 0.536, 0.603, 0.498], array_map(fn (array $row): float => round($row['ndvi'], 3), $analysis['districts']));

            return true;
        });
        $response->assertSeeText('0,498')->assertSeeText('-0,472')->assertSeeText('0,1188');
        $response->assertDontSeeText('Pertahankan kelembapan tanah 60');
        $response->assertDontSeeText('menunjukkan kondisi daun saat ini');
        $response->assertDontSeeText('0.23160304422198');
    }

    public function test_missing_analysis_data_is_not_presented_as_zero_or_growth_measurements(): void
    {
        $view = $this->view('partials.condition-analysis', [
            'conditionAnalysis' => ['current' => ['NDVI' => null, 'NDWI' => null, 'B12' => null], 'annual' => [], 'districts' => []],
        ]);

        $view->assertSee('Belum tersedia');
        $view->assertSee('Data tahunan belum tersedia.');
        $view->assertSee('Data kecamatan belum tersedia.');
        $view->assertDontSee('0,000');
    }
}
