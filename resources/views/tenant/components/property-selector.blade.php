@if(is_super_user())
  <div class="row mb-3 property-selector">
    <div class="col-12">
      <div class="card-border-success">
        <div class="card-body py-2">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <i class="bi bi-globe text-success me-2"></i>
              <strong class="text-success">Super User Mode:</strong>
              <span class="ms-2">
                @if(is_property_selected())
                  <span class="text-muted">Operating in <strong>{{ current_property()->name ?? 'Unknown Property' }}</strong></span>
                  <span class="badge bg-success ms-1">{{ current_property()->code ?? 'N/A' }}</span>
                @else
                  <span class="text-warning">Global View</span>
                @endif
              </span>
            </div>
            
            <div class="d-flex align-items-center">
              @if(is_property_selected())
                <a href="{{ request()->fullUrlWithQuery(['clear_property' => '1']) }}" 
                   class="btn btn-outline-warning btn-sm me-2">
                  <i class="bi bi-x-circle"></i> Exit Property
                </a>
              @endif
              
              <div class="dropdown">
                <button class="btn btn-success btn-sm dropdown-toggle" type="button" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-building"></i> Switch Property
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  @if(is_property_selected())
                    <li>
                      <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['switch_property' => 'all']) }}">
                        <i class="bi bi-globe text-warning"></i> Global View
                      </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                  @endif
                  
                  @php
                    $properties = \App\Models\Tenant\Property::active()->orderBy('name')->get();
                  @endphp
                  
                  @forelse($properties as $property)
                    <li>
                      <a class="dropdown-item {{ is_property_selected() && selected_property_id() == $property->id ? 'active' : '' }}" 
                         href="{{ request()->fullUrlWithQuery(['switch_property' => $property->id]) }}">
                        <i class="bi bi-building"></i> {{ $property->name }}
                        <small class="text-muted d-block">{{ $property->code }}</small>
                      </a>
                    </li>
                  @empty
                    <li>
                      <span class="dropdown-item-text text-muted">
                        <i class="bi bi-info-circle"></i> No properties available
                      </span>
                    </li>
                  @endforelse
                  
                  @if($properties->count() > 0)
                    <li><hr class="dropdown-divider"></li>
                  @endif
                  <li>
                    <a class="dropdown-item text-success" href="{{ route('tenant.properties.create') }}">
                      <i class="bi bi-plus-circle"></i> Create New Property
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Property Selector JavaScript --}}
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get the property selector dropdown
    const propertyDropdown = document.querySelector('.property-selector .dropdown');
    const dropdownToggle = document.querySelector('.property-selector .dropdown-toggle');
    const dropdownMenu = document.querySelector('.property-selector .dropdown-menu');
    const body = document.body;
    
    if (propertyDropdown && dropdownToggle && dropdownMenu) {
      
      // Function to disable other card interactions
      function disableOtherCards() {
        body.classList.add('dropdown-overlay-active');
      }
      
      // Function to re-enable other card interactions
      function enableOtherCards() {
        body.classList.remove('dropdown-overlay-active');
      }
      
      // Listen for dropdown show event
      dropdownToggle.addEventListener('shown.bs.dropdown', function() {
        disableOtherCards();
        dropdownMenu.style.zIndex = '10050';
        dropdownMenu.style.position = 'absolute';
      });
      
      // Listen for dropdown hide event
      dropdownToggle.addEventListener('hidden.bs.dropdown', function() {
        enableOtherCards();
        dropdownMenu.style.zIndex = '';
        dropdownMenu.style.position = '';
      });
      
      // Ensure dropdown items are clickable
      const dropdownItems = dropdownMenu.querySelectorAll('.dropdown-item');
      dropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
          // Let the browser handle the navigation naturally
          // Bootstrap will handle closing the dropdown
        });
      });
      
      // Additional safety: close dropdown when pressing Escape
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && propertyDropdown.classList.contains('show')) {
          dropdownToggle.click();
        }
      });
    }
  });
  </script>
@endif