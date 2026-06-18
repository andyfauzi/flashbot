@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('code', '403')
@section('message')
    {{ $exception->getMessage() ?: 'Akses ditolak.' }}
@endsection
