<?php

namespace Tests\Feature;

use Tests\TestCase;

class DistrictReportTest extends TestCase
{
    public function test_all_districts_share_the_same_screen_and_report_data(): void
    {
        $this->get('/')->assertOk()->assertViewHas('reportData', function (array $data): bool {
            $this->assertCount(12, $data['rows']);
            $this->assertCount(4, $data['areas']);
            $this->assertSame('Semua kecamatan', $data['scope']);

            return true;
        })->assertSee('research-report-shell')->assertSee('Simpan sebagai PDF');
    }

    public function test_each_map_selection_filters_rows_and_candidate_areas(): void
    {
        foreach (['Bagor', 'Gondang', 'Rejoso', 'Sukomoro'] as $district) {
            $this->get('/?district='.strtolower($district))->assertOk()->assertViewHas('reportData', function (array $data) use ($district): bool {
                $this->assertSame($district, $data['scope']);
                $this->assertCount(3, $data['rows']);
                $this->assertSame([$district], array_values(array_unique(array_column($data['rows'], 'district'))));
                $this->assertSame([2023, 2024, 2025], array_column($data['rows'], 'year'));
                $this->assertCount(1, $data['areas']);
                $this->assertSame($district, $data['areas'][0]['Kecamatan']);
                $this->assertSame(12, $data['metrics']['Observations']);

                return true;
            });
        }
    }

    public function test_gondang_values_match_export_and_error_uses_unrounded_values(): void
    {
        $this->get('/?district=gondang')->assertViewHas('reportData', function (array $data): bool {
            $row = $data['rows'][2];
            $this->assertEqualsWithDelta(9.508433296982915, $row['actual'], 0.0000001);
            $this->assertEqualsWithDelta(10.640151977539062, $row['prediction'], 0.0000001);
            $this->assertEqualsWithDelta(1.131718680556147, $row['error'], 0.0000001);
            $this->assertEqualsWithDelta(6203.225784468518, (float) $data['areas'][0]['DOA_Filtered_Ha_T0_5'], 0.000001);

            return true;
        });
    }

    public function test_initial_district_keeps_other_reports_available_for_instant_switching(): void
    {
        $this->get('/?district=gondang')->assertOk()
            ->assertViewHas('activeDistrict', 'gondang')
            ->assertViewHas('districtReports', function (array $reports): bool {
                $this->assertSame(['all', 'bagor', 'gondang', 'rejoso', 'sukomoro'], array_keys($reports));
                $this->assertCount(12, $reports['all']['rows']);
                $this->assertCount(4, $reports['all']['latestRows']);
                foreach (['bagor', 'gondang', 'rejoso', 'sukomoro'] as $key) {
                    $this->assertCount(3, $reports[$key]['rows']);
                    $this->assertCount(1, $reports[$key]['latestRows']);
                    $this->assertSame(2025, $reports[$key]['latestRows'][0]['year']);
                    $this->assertSame(ucfirst($key), $reports[$key]['latestRows'][0]['district']);
                }

                return true;
            })->assertSeeText('Prediksi Produktivitas')->assertSeeText('Lihat laporan lengkap / PDF');
    }

    public function test_unknown_or_array_district_is_rejected(): void
    {
        $this->get('/?district=unknown')->assertNotFound();
        $this->get('/?district[]=gondang')->assertNotFound();
    }
}
