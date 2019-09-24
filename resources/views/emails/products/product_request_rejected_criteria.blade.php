@extends('emails.base')

@section('title', mail_trans('product_request_rejected.title', compact('productName')))
@section('html')
    {{ mail_trans('dear_citizen') }}
    <br/>
    <br/>
    {!! nl2br(e(mail_trans('product_request_rejected.message', compact('productName')))) !!}
    <br/>
    <br/>
@endsection
