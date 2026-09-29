@php
  $emailLogoUrl = asset('vastu/images/logo.png');
@endphp
<tr>
  <td align="center" style="padding:0 0 22px;">
    <a href="{{ route('home') }}" style="text-decoration:none;border:0;">
      <img
        src="{{ $emailLogoUrl }}"
        width="160"
        alt="Vastutathastu"
        style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;max-width:160px;height:auto;"
      >
    </a>
  </td>
</tr>
