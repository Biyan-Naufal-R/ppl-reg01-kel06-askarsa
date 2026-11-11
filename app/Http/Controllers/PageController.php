<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class PageController extends Controller
{
 private function serviceData(){return [
 ['slug'=>'bima-saputra','initials'=>'BS','name'=>'Bima Saputra','district'=>'Purwokerto Utara','category'=>'Antar & titip beli','description'=>'Bantu belanja, antar barang, dan urusan harian Anda.','price'=>'Rp25.000'],
 ['slug'=>'siti-rahmawati','initials'=>'SR','name'=>'Siti Rahmawati','district'=>'Sokaraja','category'=>'Bersih rumah','description'=>'Rumah kembali nyaman, rapi, dan bersih menyeluruh.','price'=>'Rp75.000'],
 ['slug'=>'dimas-prasetyo','initials'=>'DP','name'=>'Dimas Prasetyo','district'=>'Purwokerto Timur','category'=>'Perbaikan ringan','description'=>'Bantuan praktis untuk perawatan rumah sehari-hari.','price'=>'Rp50.000'],
 ['slug'=>'rina-wulandari','initials'=>'RW','name'=>'Rina Wulandari','district'=>'Baturaden','category'=>'Pendamping harian','description'=>'Teman bantu urusan keluarga dan kebutuhan harian.','price'=>'Rp40.000']];}
 private function jobsData(){return [
 ['slug'=>'desainer-konten-media-sosial','company'=>'Studio Karsa Banyumas','title'=>'Desainer Konten Media Sosial','type'=>'Freelance','mode'=>'Remote','location'=>'Purwokerto Timur, Banyumas','salary'=>'Rp1.500.000 / proyek'],
 ['slug'=>'staf-administrasi','company'=>'CV Satria Niaga','title'=>'Staf Administrasi','type'=>'Full-Time','mode'=>'On-site','location'=>'Purwokerto Selatan, Banyumas','salary'=>'Rp2.500.000–3.000.000 / bulan'],
 ['slug'=>'fotografer-produk-umkm','company'=>'Kedai Kopi Lereng','title'=>'Fotografer Produk UMKM','type'=>'Freelance','mode'=>'On-site','location'=>'Baturaden, Banyumas','salary'=>'Rp800.000 / proyek'],
 ['slug'=>'barista','company'=>'Kedai Kopi Lereng','title'=>'Barista','type'=>'Full-Time','mode'=>'On-site','location'=>'Baturaden, Banyumas','salary'=>'Rp2.300.000–2.800.000 / bulan']];}
 public function home(){return view('pages.home',['services'=>$this->serviceData(),'jobs'=>$this->jobsData()]);}
 public function services(){return view('pages.services',['services'=>$this->serviceData()]);}
 public function service($slug){$service=collect($this->serviceData())->firstWhere('slug',$slug);abort_unless($service,404);return view('pages.service-detail',compact('service'));}
 public function jobs(Request $request,$type='all'){ $jobs=collect($this->jobsData())->when($type!=='all',fn($q)=>$q->where('type',$type==='freelance'?'Freelance':'Full-Time'))->values()->all();return view('pages.jobs',['jobs'=>$jobs,'type'=>$type]);}
 public function job($slug){$job=collect($this->jobsData())->firstWhere('slug',$slug);abort_unless($job,404);return view('pages.job-detail',compact('job'));}
 public function auth($mode){return view('auth.form',compact('mode'));}
 public function dashboard($page){return view('dashboard.index',compact('page'));}
 public function admin($page){return view('admin.index',compact('page'));}
}
