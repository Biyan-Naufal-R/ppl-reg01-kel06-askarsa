<?php

namespace Tests\Feature;

use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_bisa_memfilter_kecamatan_pada_halaman_freelance(): void
    {
        $this->get(route('jobs.freelance', ['kecamatan' => 'Sokaraja']))
            ->assertOk()
            ->assertSee('Desainer Grafis Part Time')
            ->assertDontSee('Desainer Konten Media Sosial')
            ->assertDontSee('Staf Event Part Time');
    }

    public function test_tamu_bisa_memfilter_kategori_freelance_dan_part_time(): void
    {
        $this->get(route('jobs.freelance', ['kategori' => 'Freelance']))
            ->assertOk()
            ->assertSee('Desainer Konten Media Sosial')
            ->assertDontSee('Staf Event Part Time');

        $this->get(route('jobs.freelance', ['kategori' => 'Part Time']))
            ->assertOk()
            ->assertSee('Staf Event Part Time')
            ->assertDontSee('Desainer Konten Media Sosial');
    }

    public function test_tamu_bisa_memfilter_metode_kerja(): void
    {
        $this->get(route('jobs.freelance', ['metode' => 'remote']))
            ->assertOk()
            ->assertSee('Desainer Grafis Part Time')
            ->assertDontSee('Staf Event Part Time');

        $this->get(route('jobs.freelance', ['metode' => 'onsite']))
            ->assertOk()
            ->assertSee('Staf Event Part Time')
            ->assertDontSee('Desainer Grafis Part Time');
    }

    public function test_filter_jenis_menampilkan_badge_warna(): void
    {
        $this->get(route('jobs.freelance', ['jenis' => 'event']))
            ->assertOk()
            ->assertSee('1 proyek freelance ditemukan')
            ->assertSee('Staf Event Part Time')
            ->assertSee('bg-rose-50')
            ->assertDontSee('Desainer Grafis Part Time');
    }

    public function test_filter_bayaran_memakai_irisan_rentang(): void
    {
        $this->get(route('jobs.freelance', ['gaji_min' => 900000, 'gaji_max' => 950000]))
            ->assertOk()
            ->assertSee('Staf Event Part Time')
            ->assertSee('Barista Part Time')
            ->assertDontSee('Relawan Sosial Part Time')
            ->assertDontSee('Teknisi IT Part Time');
    }

    public function test_filter_gabungan_bisa_dipakai_user_yang_sudah_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('jobs.freelance', [
                'kecamatan' => 'Purwokerto Selatan',
                'kategori' => 'Part Time',
                'metode' => 'onsite',
                'jenis' => 'event',
                'gaji_min' => 700000,
                'gaji_max' => 900000,
            ]))
            ->assertOk()
            ->assertSee('Staf Event Part Time')
            ->assertDontSee('Tutor Pendidikan Part Time')
            ->assertDontSee('Desainer Konten Media Sosial');
    }

    public function test_pencarian_keyword_menyaring_judul_dan_pemberi_kerja(): void
    {
        $this->get(route('jobs.freelance', ['q' => 'Event']))
            ->assertOk()
            ->assertSee('Staf Event Part Time')
            ->assertDontSee('Desainer Grafis Part Time');
    }

    public function test_reset_filter_menampilkan_semua_lowongan_freelance_dan_part_time(): void
    {
        $this->get(route('jobs.freelance', ['jenis' => 'event']))
            ->assertOk()
            ->assertSee('1 proyek freelance ditemukan');

        $this->get(route('jobs.freelance'))
            ->assertOk()
            ->assertSee('12 proyek freelance ditemukan')
            ->assertSee('Desainer Konten Media Sosial')
            ->assertSee('Staf Event Part Time');
    }

    public function test_badge_freelance_part_time_dan_remote_onsite_berbeda_warna(): void
    {
        $this->get(route('jobs.freelance', ['kategori' => 'Freelance']))
            ->assertOk()
            ->assertSee('bg-teal-50');

        $this->get(route('jobs.freelance', ['kategori' => 'Part Time']))
            ->assertOk()
            ->assertSee('bg-orange-50');

        $this->get(route('jobs.freelance', ['metode' => 'remote']))
            ->assertOk()
            ->assertSee('bg-blue-50');

        $this->get(route('jobs.freelance', ['metode' => 'onsite']))
            ->assertOk()
            ->assertSee('bg-indigo-50');
    }

    public function test_halaman_full_time_menampilkan_filter_yang_sudah_berfungsi(): void
    {
        $this->get(route('jobs.fulltime'))
            ->assertOk()
            ->assertSee('Staf Administrasi')
            ->assertSee('Barista')
            ->assertSee('2 lowongan full-time ditemukan')
            ->assertSee('Kecamatan')
            ->assertSee('Metode kerja')
            ->assertSee('Pendidikan minimum')
            ->assertSee('Gaji bulanan minimal')
            ->assertSee('Terapkan Filter')
            ->assertSee('Reset filter')
            ->assertDontSee('Staf Event Part Time');
    }

    public function test_tamu_bisa_memfilter_kecamatan_pada_halaman_full_time(): void
    {
        $this->get(route('jobs.fulltime', ['kecamatan' => 'Baturraden']))
            ->assertOk()
            ->assertSee('Barista')
            ->assertDontSee('Staf Administrasi');
    }

    public function test_tamu_bisa_memfilter_metode_kerja_pada_halaman_full_time(): void
    {
        $user = User::factory()->create();
        Lowongan::factory()->for($user)->create([
            'judul' => 'Staf IT Remote Full Time',
            'kategori' => 'Full-Time',
            'metode' => 'remote',
            'pendidikan_minimum' => 'd3',
        ]);

        $this->get(route('jobs.fulltime', ['metode' => 'remote']))
            ->assertOk()
            ->assertSee('Staf IT Remote Full Time')
            ->assertDontSee('Staf Administrasi')
            ->assertDontSee('Barista');
    }

    public function test_filter_pendidikan_full_time_memakai_pencocokan_persis(): void
    {
        $user = User::factory()->create();
        Lowongan::factory()->for($user)->create([
            'judul' => 'Analis Data Full Time',
            'kategori' => 'Full-Time',
            'pendidikan_minimum' => 's1',
        ]);

        $this->get(route('jobs.fulltime', ['pendidikan' => 's1']))
            ->assertOk()
            ->assertSee('Analis Data Full Time')
            ->assertDontSee('Staf Administrasi')
            ->assertDontSee('Barista');

        $this->get(route('jobs.fulltime', ['pendidikan' => 'sma']))
            ->assertOk()
            ->assertSee('2 lowongan full-time ditemukan')
            ->assertSee('Staf Administrasi')
            ->assertSee('Barista')
            ->assertDontSee('Analis Data Full Time');
    }

    public function test_filter_gaji_bulanan_full_time_memakai_irisan_rentang(): void
    {
        $this->get(route('jobs.fulltime', ['gaji_max' => 2400000]))
            ->assertOk()
            ->assertSee('1 lowongan full-time ditemukan')
            ->assertSee('Barista')
            ->assertDontSee('Staf Administrasi');
    }

    public function test_pencarian_keyword_full_time_menyaring_judul_dan_pemberi_kerja(): void
    {
        $this->get(route('jobs.fulltime', ['q' => 'Barista']))
            ->assertOk()
            ->assertSee('Barista')
            ->assertDontSee('Staf Administrasi');
    }

    public function test_reset_filter_menampilkan_semua_lowongan_full_time(): void
    {
        $this->get(route('jobs.fulltime', ['kecamatan' => 'Baturraden']))
            ->assertOk()
            ->assertSee('1 lowongan full-time ditemukan');

        $this->get(route('jobs.fulltime'))
            ->assertOk()
            ->assertSee('2 lowongan full-time ditemukan')
            ->assertSee('Staf Administrasi')
            ->assertSee('Barista');
    }

    public function test_detail_lowongan_part_time_menampilkan_badge_dan_bayaran_proyek(): void
    {
        $this->get(route('jobs.show', 'staf-event-part-time'))
            ->assertOk()
            ->assertSee('Staf Event Part Time')
            ->assertSee('Bayaran proyek')
            ->assertSee('bg-orange-50')
            ->assertSee('bg-indigo-50')
            ->assertSee('bg-rose-50');
    }
}
