@extends('layouts.app')
@section('content')
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
        @can($modul . '.bipot')
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Jumlah BIPOT</p>
                                <h4 class="my-1 text-info">{{ $bipot }}</h4>
                                <p class="mb-0 font-13">Sinkronisasi terakhir</p>
                            </div>
                            <a href="{{ route($modul . '.bipot') }}"
                                class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i
                                    class='bx bx-sync'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        @can($modul . '.bipotperangkatan')
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Jumlah BIPOT Per Angkatan</p>
                                <h4 class="my-1 text-info">{{ $bipot_per_angkatan }}</h4>
                                <p class="mb-0 font-13">Sinkronisasi terakhir</p>
                            </div>
                            <a href="{{ route($modul . '.bipotperangkatan') }}"
                                class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i
                                    class='bx bx-sync'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        @can($modul . '.bipotpersemester')
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Jumlah BIPOT Per Semester</p>
                                <h4 class="my-1 text-info">{{ $bipot_per_semester }}</h4>
                                <p class="mb-0 font-13">Sinkronisasi terakhir</p>
                            </div>
                            <a href="{{ route($modul . '.bipotpersemester') }}"
                                class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i
                                    class='bx bx-sync'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        @can($modul . '.tagihan')
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Jumlah Tagihan</p>
                                <h4 class="my-1 text-info">{{ $tagihan }}</h4>
                                <p class="mb-0 font-13">Sinkronisasi terakhir</p>
                            </div>
                            <a href="{{ route($modul . '.tagihan') }}"
                                class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i
                                    class='bx bx-sync'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        @can($modul . '.pembayaran')
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Jumlah Pembayaran</p>
                                <h4 class="my-1 text-info">{{ $pembayaran }}</h4>
                                <p class="mb-0 font-13">Sinkronisasi terakhir</p>
                            </div>
                            <a href="{{ route($modul . '.pembayaran') }}"
                                class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i
                                    class='bx bx-sync'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection
