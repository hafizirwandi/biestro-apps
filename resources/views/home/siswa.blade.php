@extends('layouts.main-layout.app')
@section('title', 'Home')
@section('css')
    <link rel="stylesheet" href="{{ asset('vuexy/assets/vendor/css/pages/page-faq.css') }}" />
@endsection
@section('content')
    <div class="faq-header d-flex flex-column justify-content-center align-items-center rounded h-px-300 position-relative">
        <img src="{{ asset('vuexy/assets/img/pages/header.png') }}" class="scaleX-n1-rtl faq-banner-img"
            alt="background image">
        <h3 class="text-center"> Hai, {{ auth()->guard('siswa')->user()->nama }} </h3>

        <p class="text-center mb-0 px-3">Selamat datang di CAT (Computer Assisted Test)</p>
        <a href="{{ route('cat') }}" class="btn btn-primary btn-lg mt-5">Mulai Ujian</a>
    </div>
@endsection
