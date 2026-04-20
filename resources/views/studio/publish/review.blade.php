@extends('layouts.studio')

@section('content')
<div>
    <div id="review-screen-mount">
        <review-screen version-id="{{ $versionId }}"></review-screen>
    </div>
</div>
@endsection
