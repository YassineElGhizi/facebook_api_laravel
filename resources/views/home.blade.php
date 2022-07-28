@include('components.bootsrap_imports')

<div class="container">

    @if(Session::has('message'))
        <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card p-4">
                <div class=" image d-flex flex-column justify-content-center align-items-center">
                    <button class="btn btn-secondary">
                        <img src="{{Auth::user()->picture}}" height="100" width="100"/>
                    </button>
                    <span class="name mt-3">{{Auth::user()->name}}</span>
                    <span class="idd">{{str_replace("@Gh'z" , "@gmail.com" , Auth::user()->email)}}</span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Listes Pages') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table class="table table-bordered" border="1">
                        <thead>
                        <tr>
                            <th scope="col">Logo</th>
                            <th scope="col">Id page</th>
                            <th scope="col">Name page</th>
                            <th scope="col">Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($pages as $item)
                            <tr>
                                <th scope="row">
                                    <img class="imgPage" style="width: 5rem" src="{{$item->image}}"/>
                                </th>
                                <td>{{$item->id}}</td>
                                <td>{{$item->name}}</td>
                                <td>
                                    <a href="/get_post/{{$item->id}}-{{$item->access_token}}" class="btn btn-info">
                                        Go To Posts
                                    </a>
                                    <a href="/mail/{{$item->id}}-{{$item->access_token}}" class="btn btn-dark">
                                        Get Mail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

