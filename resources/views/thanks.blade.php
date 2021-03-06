@extends('layouts.front')

@section('content')
    <h2 class="alert alert-success">
        Muito Obrigado por sua compra!
    </h2>
    <h3>
        Seu pedido foi processado, código do pedido: {{request()->get('order')}}.

        @if(request()->has('b'))
            <a href="{{request()->get('b')}}" class="btn btn-lg- btn-success" target="_blank">IMPRIMIR BOLETO</a>
        @endif
    </h3>
@endsection