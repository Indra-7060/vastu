@include('frontend.partials.site-config')
@php $megaMenu = $megaMenu ?? \App\Support\MegaMenu::data(); @endphp
<nav id="menu">
  {{-- Same items as the desktop menu. --}}
  <ul>
    <li>
      <span>PRODUCTS</span>
      <ul>
        <li><a href="{{ route('shop') }}">ALL PRODUCTS</a></li>
        @foreach($megaMenu['categories'] as $category)
          <li><a href="{{ $category->frontendUrl() }}">{{ strtoupper($category->title) }}</a></li>
        @endforeach
      </ul>
    </li>
    <li>
      <span>SERVICES</span>
      <ul>
        <li><a href="{{ route('services') }}">ALL SERVICES</a></li>
        @foreach($megaMenu['services'] as $group)
          <li>
            <span>{{ strtoupper($group['title']) }}</span>
            <ul>
              <li><a href="{{ route('info', $group['page']) }}">ABOUT {{ strtoupper($group['title']) }}</a></li>
              @foreach($group['products'] as $service)
                <li><a href="{{ route('shop.single', $service->slug) }}">{{ strtoupper($service->title) }}</a></li>
              @endforeach
            </ul>
          </li>
        @endforeach
        <li><a href="{{ route('info', 'book-a-consultation') }}">BOOK A CONSULTATION</a></li>
      </ul>
    </li>
    <li><a href="{{ route('gallery') }}">GALLERY</a></li>
    <li><a href="{{ route('contact') }}">CONTACT US</a></li>
  </ul>
</nav>
