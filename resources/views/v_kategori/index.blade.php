@extends('layouts.adm-main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <h1 class="h3 mt-3 ml-3 text-gray-800">Kategori</h1>
                    @if(Session::has('Gagal'))
                        <div class="alert alert-danger" role="alert">
                            {{ Session::get('Gagal') }}
                        </div>
                    @endif

                    @if(Session::has('success'))
                        <div class="alert alert-success" role="alert">
                            {{ Session::get('success') }}
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <a href="{{ route('kategori.create') }}" class="btn btn-md btn-success">TAMBAH KATEGORI</a>
                            <form action="{{ route('search_kategori') }}" method="get" class="form-inline d-flex mw-100 navbar-search">
                                <div class="input-group">
                                    <input type="text" name="query" class="form-control bg-light border-0 small" placeholder="Cari Kategori..." aria-label="Search" aria-describedby="basic-addon2" value="{{ request('query') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search fa-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 7%">ID</th>
                            <th>DESKRIPSI</th>
                            <th>KATEGORI</th>
                            <th>Keterangan Kategori</th>
                            <th style="width: 15%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rsetKategori as $kategori)
                            <tr>
                                <td>{{ $kategori->id  }}</td>
                                <td>{{ $kategori->deskripsi  }}</td>
                                <td>{{ $kategori->kategori  }}</td>
                                <td>{{ $kategori->ketKategori   }}</td>
                                <td class="text-center"> 
                                    <form onsubmit="return confirm('Apakah Anda Yakin ?');" 
                                    action="{{ route('kategori.destroy', $kategori->id) }}" method="POST">

                                        <a href="{{ route('kategori.show', $kategori->id) }}" 
                                        class="btn btn-sm btn-dark"><i class="fa fa-eye"></i></a>

                                        <a href="{{ route('kategori.edit', $kategori->id) }}" 
                                        class="btn btn-sm btn-primary"><i class="fa fa-pencil-alt"></i></a>
                                        
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                                
                            </tr>
                        @empty
                            <div class="alert">
                                Data Kategori belum tersedia
                            </div>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection