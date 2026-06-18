@extends('errors.layout')

@section('title', 'Tidak Ditemukan')
@section('code', '404')
@section('message')
    {{ $exception->getMessage() ?: 'Halaman tidak ditemukan.' }}
@endsection
