@if(session()->has('flash_message'))
    <div class="row container-fluid">

        <?php
            $messageImportant = session()->has('flash_message_important') && session('flash_message_important');
            $alertType = session()->has('flash_message_alert_class') ? session('flash_message_alert_class') : '';
        ?>

        <div class="alert {{ $alertType }} {{ $messageImportant ? 'alert-important' : '' }}">

            @if(session()->has('flash_message_important'))
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            @endif

            {{ session('flash_message') }}
        </div>
    </div>
@endif