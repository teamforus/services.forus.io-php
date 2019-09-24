@extends('emails.base')

@section('title', mail_trans('product_request_rejected.title', compact('recordTypeName', 'recordTypeValue', 'productName')))
@section('html')
    {{ mail_trans('dear_citizen') }}
    <br/>
    <br/>
    {!! nl2br(e(mail_trans('product_request_rejected.message', compact('recordTypeName', 'recordTypeValue', 'productName')))) !!}
    <br/>
    <br/>
@endsection
