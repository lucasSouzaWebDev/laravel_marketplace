@extends('layouts.front')

@section('content')
    <div class="row front">
        <div class="col-12 text-center">
            <h2>{{$category->name}}</h2>
            <hr>
        </div>
        @forelse($category->products as $key => $product)
            <div class="col-md-3">
                <div class="card card-product text-center" style="width: 98%;">
                    @if($product->photos->count())
                        <img src="{{asset('storage/' . $product->photos->first()->image)}}" alt="" class="card-img-top">
                    @else
                        <img src="{{asset('assets/img/no-photo.jpg')}}" alt="Produto sem foto" class="card-img-top">
                    @endif
                    <div class="card-body">
                        <h2 class="card-title">{{$product->name}}</h2>
                        <p class="card-text">{{$product->description}}</p>
                        <h3>R$ {{number_format($product->price, '2', ',', '.')}}</h3>
                        <a href="{{route('product.single', ['slug' => $product->slug])}}" class="btn btn-success">
                            Ver Produto
                        </a>
                    </div>
                </div>
            </div>
            @if(($key + 1) % 4 == 0) </div><div class="row front"> @endif
        @empty
            <div class="col-12">
                <h3 class="alert alert-warning">Nenhum produto encontrado para esta categoria!</h3>
            </div>
        @endforelse
    </div>
@endsection