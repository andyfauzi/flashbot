@extends('errors.layout')

@section('title', 'Maintenance')
@section('code', '503')
@section('message')
    {{ $exception->getMessage() ?: 'Layanan sedang tidak tersedia.' }}
@endsection
