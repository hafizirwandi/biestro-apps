  <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo" style="border-bottom: 3px solid #e9a92e;">
          <a href="index.html" class="app-brand-link gap-2">
              <span class="app-brand-logo">
                  <img style="width:42px;height:42px;border-radius:50%;object-fit:cover"
                      src="{{ setting('logo') }}" alt="{{ setting('apps_name') }}">
              </span>
              <span class="app-brand-text demo menu-text fw-bold" style="color:#26499a">{{ setting('apps_name') }}</span>
          </a>

          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
              <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
              <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
          </a>
      </div>

      <div class="menu-inner-shadow"></div>

      <ul class="menu-inner py-1">
          @auth('web')
              <li class="menu-item {{ request()->routeIs('home*') ? 'active' : '' }}">
                  <a href="{{ route('home') }}" class="menu-link">
                      <i class="menu-icon tf-icons ti ti-smart-home"></i>
                      <div data-i18n="Home">Home</div>
                  </a>
              </li>
              @can('user-list')
                  <li class="menu-item {{ request()->routeIs('user*') ? 'active' : '' }}">
                      <a href="{{ route('user') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-users"></i>
                          <div data-i18n="User">User</div>
                      </a>
                  </li>
              @endcan
              @canany(['role-list', 'permission-list'])
                  <li class="menu-item {{ isDropdown(['role*', 'permission*']) }}">
                      <a href="javascript:void(0);" class="menu-link menu-toggle">
                          <i class="menu-icon tf-icons ti ti-settings"></i>
                          <div data-i18n="Role & Permission">Role & Permission</div>
                      </a>
                      <ul class="menu-sub">
                          @can('role-list')
                              <li class="menu-item {{ request()->routeIs('role*') ? 'active' : '' }}">
                                  <a href="{{ route('role') }}" class="menu-link">
                                      <div data-i18n="Role">Role</div>
                                  </a>
                              </li>
                          @endcan
                          @can('permission-list')
                              <li class="menu-item {{ request()->routeIs('permission*') ? 'active' : '' }}">
                                  <a href="{{ route('permission') }}" class="menu-link">
                                      <div data-i18n="Permission">Permission</div>
                                  </a>
                              </li>
                          @endcan
                      </ul>
                  </li>
              @endcanany
              @can('wahana-list')
                  <li class="menu-item {{ request()->routeIs('wahana*') ? 'active' : '' }}">
                      <a href="{{ route('wahana') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-device-gamepad-2"></i>
                          <div data-i18n="Wahana">Wahana</div>
                      </a>
                  </li>
              @endcan

              @canany(['ticket-list', 'ticket-package-list'])
                  <li class="menu-item {{ isDropdown(['ticket', 'ticket-package*']) }}">
                      <a href="javascript:void(0);" class="menu-link menu-toggle">
                          <i class="menu-icon tf-icons ti ti-ticket"></i>
                          <div data-i18n="Ticket">Ticket</div>
                      </a>
                      <ul class="menu-sub">
                          @can('ticket-list')
                              <li class="menu-item {{ request()->routeIs('ticket') ? 'active' : '' }}">
                                  <a href="{{ route('ticket') }}" class="menu-link">
                                      <div data-i18n="Ticket">Ticket</div>
                                  </a>
                              </li>
                          @endcan
                          @can('ticket-package-list')
                              <li class="menu-item {{ request()->routeIs('ticket-package*') ? 'active' : '' }}">
                                  <a href="{{ route('ticket-package') }}" class="menu-link">
                                      <div data-i18n="Ticket Package">Ticket Package</div>
                                  </a>
                              </li>
                          @endcan
                      </ul>
                  </li>
              @endcanany
              @can('counter-list')
                  <li class="menu-item {{ request()->routeIs('counter') ? 'active' : '' }}">
                      <a href="{{ route('counter') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-archive"></i>
                          <div data-i18n="Counter">Counter</div>
                      </a>
                  </li>
              @endcan
              @can('pos')
                  <li class="menu-item {{ request()->routeIs('transaction') ? 'active' : '' }}">
                      <a href="{{ route('transaction') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-shopping-bag"></i>
                          <div data-i18n="POS">POS</div>
                      </a>
                  </li>
              @endcan
              @can('free-gift-list')
                  <li class="menu-item {{ request()->routeIs('free-gift*') ? 'active' : '' }}">
                      <a href="{{ route('free-gift') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-gift"></i>
                          <div data-i18n="Free Gift">Free Gift</div>
                      </a>
                  </li>
              @endcan
              @canany(['report-transaction', 'report-ticket', 'report-revenue', 'report-popular-wahana'])
                  <li class="menu-item {{ isDropdown(['report*']) }}">
                      <a href="javascript:void(0);" class="menu-link menu-toggle">
                          <i class="menu-icon tf-icons ti ti-report"></i>
                          <div data-i18n="Report">Report</div>
                      </a>
                      <ul class="menu-sub">
                          @can('report-transaction')
                              <li class="menu-item {{ request()->routeIs('report.transaction') ? 'active' : '' }}">
                                  <a href="{{ route('report.transaction') }}" class="menu-link">
                                      <div data-i18n="Transaction">Transaction</div>
                                  </a>
                              </li>
                          @endcan


                          @can('report-ticket')
                              <li class="menu-item {{ request()->routeIs('report.ticket') ? 'active' : '' }}">
                                  <a href="{{ route('report.ticket') }}" class="menu-link">
                                      <div data-i18n="Ticket">Ticket</div>
                                  </a>
                              </li>
                          @endcan

                          @can('report-revenue')
                              <li class="menu-item {{ request()->routeIs('report.revenue') ? 'active' : '' }}">
                                  <a href="{{ route('report.revenue') }}" class="menu-link">
                                      <div data-i18n="Omset">Omset</div>
                                  </a>
                              </li>
                          @endcan

                          @can('report-popular-wahana')
                              <li class="menu-item {{ request()->routeIs('report.popular-wahana') ? 'active' : '' }}">
                                  <a href="{{ route('report.popular-wahana') }}" class="menu-link">
                                      <div data-i18n="Wahana Terpopuler">Wahana Terpopuler</div>
                                  </a>
                              </li>
                          @endcan

                          @can('playground-report')
                              <li class="menu-item {{ request()->routeIs('playground.report') ? 'active' : '' }}">
                                  <a href="{{ route('playground.report') }}" class="menu-link">
                                      <div data-i18n="Playground">Playground</div>
                                  </a>
                              </li>
                          @endcan

                          <li class="menu-item {{ request()->routeIs('report.shift') ? 'active' : '' }}">
                              <a href="{{ route('report.shift') }}" class="menu-link">
                                  <div data-i18n="Shift Cashier">Shift Cashier</div>
                              </a>
                          </li>
                          <li class="menu-item {{ request()->routeIs('report.items') ? 'active' : '' }}">
                              <a href="{{ route('report.items') }}" class="menu-link">
                                  <div data-i18n="Item Sales">Item Sales</div>
                              </a>
                          </li>
                          <li class="menu-item {{ request()->routeIs('report.payment') ? 'active' : '' }}">
                              <a href="{{ route('report.payment') }}" class="menu-link">
                                  <div data-i18n="Payment Methods">Payment Methods</div>
                              </a>
                          </li>

                      </ul>
                  </li>
              @endcanany
              @can('setting')
                  <li class="menu-item {{ request()->routeIs('setting*') ? 'active' : '' }}">
                      <a href="{{ route('setting') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-settings"></i>
                          <div data-i18n="Setting">Setting</div>
                      </a>
                  </li>
              @endcan
              @can('filemanager-view')
                  <li class="menu-item {{ request()->routeIs('filemanager*') ? 'active' : '' }}">
                      <a href="{{ route('filemanager') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-folders"></i>
                          <div data-i18n="Filemanager">Filemanager</div>
                      </a>
                  </li>
              @endcan
              @canany(['audit-trace'])
                  <li class="menu-item {{ request()->routeIs('audit-trace*') ? 'active' : '' }}">
                      <a href="{{ route('audit-trace') }}" class="menu-link">
                          <i class="menu-icon tf-icons ti ti-flip-flops"></i>
                          <div data-i18n="Log">Log</div>
                      </a>
                  </li>
              @endcanany




          @endauth

      </ul>
  </aside>


  @php

      function isDropdown($trees)
      {
          $temp = false;
          foreach ($trees as $r) {
              $temp = request()->routeIs($r);
              if ($temp) {
                  return 'active open';
                  break;
              }
          }
      }
  @endphp
