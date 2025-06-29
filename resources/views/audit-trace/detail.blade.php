 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 <div class="text-center mb-4">
     <h3 class="mb-2 modal-title">Activity Log</h3>
 </div>
 <div class="list-group">
     <div class="list-group-item">
         <h5 class="mb-1">
             {{ $r->event }} by
             @if ($r->causer_type === 'App\Models\User')
                 {{ $r->causer->name }} (Admin)
             @elseif ($r->causer_type === 'App\Models\Employee')
                 {{ $r->causer->name }} (Employee)
             @else
                 {{ $r->causer->name }} (Other)
             @endif
         </h5>
         <p class="mb-1">
             <strong>Modul:</strong> {{ $r->log_name }}
         </p>
         <strong>Properties:</strong>
         <ul class="list-group mb-1">
             @if (isset($r->properties['attributes']))
                 <li class="list-group-item">
                     <strong>New Attributes:</strong>
                     <ul>
                         @foreach ($r->properties['attributes'] as $key => $value)
                             <li>
                                 <strong>{{ $key }}:</strong>
                                 {{ is_array($value) ? json_encode($value) : $value }}
                             </li>
                         @endforeach
                     </ul>
                 </li>
             @endif

             @if (isset($r->properties['old']))
                 <li class="list-group-item">
                     <strong>Old Attributes:</strong>
                     <ul>
                         @foreach ($r->properties['old'] as $key => $value)
                             <li>
                                 <strong>{{ $key }}:</strong>
                                 {{ is_array($value) ? json_encode($value) : $value }}
                             </li>
                         @endforeach
                     </ul>
                 </li>
             @endif
         </ul>
         <small class="text-muted">Time: {{ $r->created_at->format('d M Y H:i') }}</small>
     </div>
 </div>
