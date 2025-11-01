{{-- Login Modal --}}
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      {{-- <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Login</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> --}}
      <div class="modal-body text-center">
        <h4 class="mb-0 text-success">{{ current_tenant()->name }}</h4>
        <p class="mb-0 opacity-75">Guest Portal Login</p>
        <hr>
        @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        <div class="login-form">
          <form method="POST" action="{{ route('tenant.guest-portal.send-login-link') }}">
            @csrf
            <div class="form-group">
              {{-- <label for="email">Email address</label> --}}
              <input type="email" class="form-control @error('email') is-invalid @enderror"
              id="email" name="email" placeholder="Email" value="{{ old('email') }}"
              required autofocus>
              @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
              @enderror
            </div>
            
            <button type="submit" class="theme-btn btn-style-one small bg-green">Send Login Link</button>
          </form>
          <div class="form-group bottom-text mt-3">
              <div class="text mt-2">Don’t have an account?</div>
              <a href="#" class="signup-link" id="registerLink">Register</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>