@include('components.bootsrap_imports')

<div class="container">

    @if(Session::has('message'))
        <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
    @endif


    <div class="row justify-content-center">

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        New Post
                    </button>
                </div>

                <div class="card-body">
                    <table class="table table-bordered" border="1">
                        <thead>
                        <tr>

                            <th scope="col">Post</th>
                            <th scope="col">TYPE</th>
                            <th scope="col">date</th>
                            <th scope="col">file</th>
                            <th scope="col">Action</th>

                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($posts as $item)

                            <tr>

                                <td>{{$item->message}}</td>
                                <td>{{$item->type}}</td>
                                <td> {{ $item->created_time->format('d-m-Y h:m')}}</td>
                                <td>
                                    @if($item->full_picture!=null)
                                        <img src="{{$item->full_picture}}" alt="" style="width: 5rem">
                                    @endif
                                </td>
                                <td>
                                    <form action="/delete_post" method="post">
                                        @csrf
                                        <input hidden type="text" name="page_token" value="{{$tokenPage}}">
                                        <input hidden type="text" name="page_id" value="{{$item->id_page}}">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                    <form action="/update_post" method="post">
                                        @csrf
                                        <input hidden type="text" name="page_token" value="{{$tokenPage}}">
                                        <input hidden type="text" name="page_id" value="{{$item->id_page}}">
                                        <button type="submit" class="btn btn-warning">Update</button>
                                        <input type="text" name="new_message" placeholder="New Message value">
                                    </form>

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


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="postModal" aria-hidden="true"
     width="500px">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form method="post" action="/post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="idpage" value="{{$idpage}}"/>
                <input type="hidden" name="tokenPage" value="{{$tokenPage}}"/>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="exampleFormControlTextarea1">Post Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <div class="file-upload">
                            <div class="image-upload-wrap">
                                <input class="file-upload-input" id="fileUpload" name="fileUpload" type='file'
                                       onchange="readURL(this);" accept="image/*" multiple/>
                                <div class="drag-text">
                                    <h3>file</h3>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" name="inlineCheckbox1" type="checkbox" id="inlineCheckbox1"
                               value="true">
                        <label class="form-check-label" for="inlineCheckbox1">Schedule</label>
                    </div>

                    <div class="form-group">
                        <input name="dateSchedule" id="inputSchedule" class="form-control" type="datetime-local"
                               placeholder="select date time"/>
                    </div>


                </div>
                <div class="modal-footer">

                    <button type="submit" class="btn btn-primary">Save changes</button>

                </div>

            </form>
        </div>
    </div>
</div>


