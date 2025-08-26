@extends('layouts.main-layout.app-without-menu')
@section('title', 'Ticket')
@section('content')

    <div class="d-flex justify-content-center align-items-center" style="height: 80vh;">
        <div class="col-md-6 text-center">

            <div class="alert alert-danger py-4 px-3" role="alert" style="font-size: 1.5rem;">
                <strong>Shift Already Closed!</strong>
                <p class="mt-2" style="font-size: 1.2rem;">
                    Your shift has been closed for today.<br>
                    You will be able to start a new shift tomorrow.
                </p>
            </div>

            <a href="{{ route('logout') }}" class="btn btn-lg btn-primary mt-4"> Logout </a>


        </div>
    </div>

@endsection
