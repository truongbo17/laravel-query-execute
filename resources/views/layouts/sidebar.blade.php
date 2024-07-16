<nav id="sidebar" class="active">
    <div class="custom-menu">
        <button type="button" id="sidebarCollapse" class="btn btn-primary">
            <i class="fa fa-bars"></i>
            <span class="sr-only">Toggle Menu</span>
        </button>
    </div>
    <div class="p-4">
        <h2><a href="{{route('query-execute.home')}}" class="logo">QueryExecute</a></h2>
        <ul class="list-unstyled components mb-5">
            <li @if( Route::is('query-execute.home*')) class="active" @endif>
                <a href="{{route('query-execute.home')}}"><span class="fa fa-home mr-3"></span>Home</a>
            </li>
            <li @if( Route::is('query-execute.query*')) class="active" @endif>
                <a href="{{route('query-execute.query')}}"><span class="fa fa-user mr-3"></span>Query</a>
            </li>
            <li @if( Route::is('query-execute.version*')) class="active" @endif>
                <a href="{{route('query-execute.version')}}"><span class="fa fa-briefcase mr-3"></span>Version</a>
            </li>
        </ul>
        <div class="mb-5">
            <h3 class="h6 mb-3">Subscribe for newsletter</h3>
            <form action="#" class="subscribe-form">
                <div class="form-group d-flex">
                    <div class="icon"><span class="icon-paper-plane"></span></div>
                    <input type="text" class="form-control" placeholder="Enter Email Address">
                </div>
            </form>
        </div>
        <div class="footer">
            <p>
                Contribution at: <a class="text-bold text-white" href="https://github.com/truongbo17/laravel-query-execute">
                    <span><i class="fa fa-github" aria-hidden="true"></i> truongbo17/laravel-query-execute</span>
                </a>
            </p>
        </div>
    </div>
</nav>
