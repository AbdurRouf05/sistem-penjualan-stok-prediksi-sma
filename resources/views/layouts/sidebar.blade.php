<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show {{ request()->routeIs('app.pos.*') ? 'c-sidebar-minimized' : '' }}" id="sidebar">
    <div class="c-sidebar-brand d-md-down-none">
        <a href="{{ route('home') }}" class="text-white text-decoration-none text-center">
            <span class="c-sidebar-brand-full font-weight-bold" style="font-size: 1.05rem; line-height: 1.2;">
                <i class="bi bi-basket2-fill text-warning"></i> Dua Putra<br>
                <small class="font-weight-normal text-white-50">Aneka Camilan</small>
            </span>
            <span class="c-sidebar-brand-minimized"><i class="bi bi-basket2-fill text-warning" style="font-size: 1.5rem;"></i></span>
        </a>
    </div>
    <ul class="c-sidebar-nav">
        @include('layouts.menu')
        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
        </div>
        <div class="ps__rail-y" style="top: 0px; height: 692px; right: 0px;">
            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 369px;"></div>
        </div>
    </ul>
    <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
</div>
