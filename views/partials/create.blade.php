@php
    $titleFormKey = $titleFormKey ?? 'title';
@endphp
<x-twill::input
    :name="$titleFormKey"
    :label="$titleFormKey === 'title' ? __('twill::lang.modal.title-field') : ucfirst($titleFormKey)"
    :translated="$translateTitle ?? false"
    :required="true"
    on-change="formatPermalink"
/>

@if ($permalink ?? true)
    <x-twill::input
        name="slug"
        :label="__('twill::lang.modal.permalink-field')"
        :translated="true"
        ref="permalink"
        :prefix="$permalinkPrefix ?? ''"
    />
@endif
