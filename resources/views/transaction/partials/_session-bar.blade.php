{{--
    Partial: POS Session Header Bar
    Variables: $shift (required), $showPrinterBtn (optional, default true)
    CSS: Hanya dirender jika $includeCss = true (default false).
         - Di /transaction index, CSS sudah ada secara inline.
         - Di halaman lain (sales-revenue, dll), pass $includeCss = true.
--}}
@php
    $showPrinterBtn = $showPrinterBtn ?? true;
    $includeCss = $includeCss ?? false;
@endphp

@if ($includeCss)
    <style>
        .pos-session-bar {
            background: linear-gradient(135deg, #1e1e2e 0%, #2d2d44 100%);
            color: #e0e0f0;
            border-radius: 10px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 18px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        }

        .pos-session-bar .session-info {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .pos-session-bar .session-item {
            display: flex;
            flex-direction: column;
            min-width: 80px;
        }

        .pos-session-bar .session-item .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #9e9ec0;
        }

        .pos-session-bar .session-item .value {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .pos-session-bar .divider {
            width: 1px;
            height: 30px;
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-printer {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e0e0f0;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-printer:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .btn-printer.connected {
            background: rgba(40, 199, 111, 0.15);
            border-color: #28c76f;
            color: #28c76f;
            box-shadow: 0 0 10px rgba(40, 199, 111, 0.2);
        }

        .btn-printer.connected:hover {
            background: rgba(40, 199, 111, 0.25);
        }

        .printer-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #9e9ec0;
            transition: background 0.3s;
            flex-shrink: 0;
        }

        .printer-dot.connected {
            background: #28c76f;
            box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
            animation: printerPulse 1.8s infinite;
        }

        @keyframes printerPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(40, 199, 111, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(40, 199, 111, 0);
            }
        }
    </style>
@endif

{{-- Three-dot dropdown CSS — ALWAYS rendered --}}
<style>
    .btn-dots {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        background: rgba(255, 255, 255, 0.07);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #e0e0f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .btn-dots:hover {
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.35);
    }

    .pos-dots-wrap {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pos-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 210px;
        background: #1e1e2e;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45);
        z-index: 99999;
        overflow: hidden;
        display: none;
    }

    .pos-dropdown-menu.show {
        display: block;
        animation: posDropFade 0.15s ease;
    }

    @keyframes posDropFade {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pos-dropdown-menu a,
    .pos-dropdown-menu button.pdm-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 11px 16px;
        color: #d0d0f0;
        background: transparent;
        border: none;
        font-size: 13px;
        text-decoration: none;
        transition: background 0.15s;
        cursor: pointer;
        text-align: left;
    }

    .pos-dropdown-menu a:hover,
    .pos-dropdown-menu button.pdm-btn:hover {
        background: rgba(255, 255, 255, 0.09);
        color: #fff;
    }

    .pos-dropdown-menu .pdm-divider {
        border-top: 1px solid rgba(255, 255, 255, 0.09);
        margin: 4px 0;
    }

    .pos-dropdown-menu .pdm-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #888;
        padding: 8px 16px 4px;
    }
</style>

<div class="pos-session-bar">
    <div class="session-info">
        <div class="session-item">
            <span class="label">Kasir</span>
            <span class="value">{{ auth()->user()->name }}</span>
        </div>
        <div class="divider"></div>
        <div class="session-item">
            <span class="label">Counter</span>
            <span class="value">{{ $shift->counter->name ?? '-' }}</span>
        </div>
        <div class="divider"></div>
        <div class="session-item">
            <span class="label">Buka Shift</span>
            <span class="value">{{ \Carbon\Carbon::parse($shift->opened_at)->format('H:i') }}</span>
        </div>
        <div class="divider"></div>
        <div class="session-item">
            <span class="label">Tanggal</span>
            <span class="value">{{ \Carbon\Carbon::parse($shift->opened_at)->format('d M Y') }}</span>
        </div>
        <div class="divider"></div>
        <div class="session-item">
            <span class="label">Status</span>
            <span class="value">
                <span class="badge {{ $shift->status === 'open' ? 'bg-success' : 'bg-secondary' }}">
                    {{ ucfirst($shift->status) }}
                </span>
            </span>
        </div>
    </div>

    @if ($showPrinterBtn)
        <div class="pos-dots-wrap" id="posDotsWrap">
            <button class="btn-printer" id="btnConnectBT" onclick="connectBTPrinter()"
                title="Klik untuk connect/disconnect printer">
                <span class="printer-dot" id="printerDot"></span>
                <i class="ti ti-printer"></i>
                <span id="btnConnectBTLabel">Connect Printer</span>
            </button>

            <button class="btn-dots" id="posDotsMenuBtn" title="Menu" type="button">
                <i class="ti ti-dots-vertical" style="font-size:16px;"></i>
            </button>

            <div class="pos-dropdown-menu" id="posDotsMenu">
                <div class="pdm-label">Menu</div>

                @php $pageCtx = $pageContext ?? 'index'; @endphp

                {{-- Back to POS (tampil di semua halaman kecuali index) --}}
                @if ($pageCtx !== 'index')
                    <a href="{{ route('transaction') }}">
                        <i class="ti ti-arrow-left text-secondary"></i> Kembali ke POS
                    </a>
                    <div class="pdm-divider"></div>
                @endif

                {{-- Voucher List — hanya di index --}}
                @if ($pageCtx === 'index')
                    <button class="pdm-btn"
                        onclick="document.getElementById('voucher-list')?.click(); document.getElementById('posDotsMenu').classList.remove('show');">
                        <i class="ti ti-ticket text-primary"></i> Voucher List
                    </button>
                @endif

                {{-- Sales Revenue — semua halaman kecuali sales-revenue itu sendiri --}}
                @if ($pageCtx !== 'sales-revenue')
                    <button class="pdm-btn"
                        onclick="window.open('{{ route('transaction.sales-revenue') }}', '_blank'); document.getElementById('posDotsMenu').classList.remove('show');">
                        <i class="ti ti-chart-bar text-success"></i> Sales Revenue
                    </button>
                @endif

                {{-- Ticket Sold — semua halaman --}}
                <button class="pdm-btn"
                    onclick="openTicketSoldModal(); document.getElementById('posDotsMenu').classList.remove('show');">
                    <i class="ti ti-chart-pie text-info"></i> Ticket Sold
                </button>

                {{-- Wahana Sold — semua halaman --}}
                <button class="pdm-btn"
                    onclick="openWahanaSoldModal(); document.getElementById('posDotsMenu').classList.remove('show');">
                    <i class="ti ti-chart-bar text-warning" style="color:#d97706 !important;"></i> Wahana Sold
                </button>

                <div class="pdm-divider"></div>

                {{-- Cashier Closing — semua halaman kecuali close-shift itu sendiri --}}
                @if ($pageCtx !== 'close-shift')
                    <button class="pdm-btn" onclick="window.location.href='{{ route('transaction.close-shift') }}'">
                        <i class="ti ti-lock text-danger"></i> Cashier Closing
                    </button>
                    <div class="pdm-divider"></div>
                @endif

                <a href="{{ route('logout') }}" style="color:#ff7272;">
                    <i class="ti ti-logout"></i> Logout
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    (function() {
        var dotsBtn = document.getElementById('posDotsMenuBtn');
        var dotsMenu = document.getElementById('posDotsMenu');
        if (!dotsBtn || !dotsMenu) return;

        dotsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dotsMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!dotsMenu.contains(e.target) && e.target !== dotsBtn) {
                dotsMenu.classList.remove('show');
            }
        });
    })();
</script>
