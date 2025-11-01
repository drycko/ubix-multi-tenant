{{-- Register Modal --}}
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
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
        <p class="mb-0 opacity-75">Create your account</p>
        <hr>
        <div class="alert alert-info">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Property Notice: </strong>Please ensure you register using the email address associated with your booking for a seamless experience.
        </div>
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
        <div class="login-form register">
          <form method="POST" action="{{ route('tenant.register.store') }}">
            @csrf
            <div class="form-group">
              <input type="text" class="form-control @error('name') is-invalid @enderror"
              id="name" name="name" placeholder="Name" value="{{ old('name') }}"
              required>
            </div>
            <div class="form-group">
              {{-- <label for="email">Email address</label> --}}
              <input type="email" class="form-control @error('email') is-invalid @enderror"
              id="email" name="email" placeholder="Email" value="{{ old('email') }}"
              required>
            </div>
            <div class="form-group">
              {{-- <label for="password">Password</label> --}}
              <input type="password" class="form-control @error('password') is-invalid @enderror"
              id="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
              {{-- <label for="password_confirmation">Confirm Password</label> --}}
              <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
              id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required>
            </div>
            
            <div class="form-group">
                <div class="checkbox-wrap">
                    <input type='checkbox' name='agree-terms' value='agree-terms' id="agree-terms" required />
                    <label for="agree-terms">I agree to our <a href="#">Terms and Conditions</a></label>
                </div>
            </div>
            <div class="form-group">
                <small>By registering, I agree to the {{ current_tenant()->name }} <a href="#">Terms and Conditions</a></small>
            </div>
            <button type="submit" class="theme-btn btn-style-one small bg-green">Register</button>
          </form>
          <div class="form-group bottom-text mt-3">
              <div class="text mt-2">Already have an account?</div>
              <a href="javascript:void(0);" class="signup-link" id="loginLink">Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Register Modal --}}