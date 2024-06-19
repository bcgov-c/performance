<div class="d-inline-flex flex-row align-items-center">
    <div class="bg-{{ \Config::get("global.status.$status.color") }} rounded-circle mr-2" style="width:15px; height:15px;"></div>
    <div class="text-capitalize">
        {{ $status }}
        <!-- <i class="fas fa-info-circle" data-trigger="hover" data-toggle="popover" data-placement="right" data-html="true" data-content='{{ \Config::get("global.status.$status.tooltip") }}'></i> -->
    </div>
</div>