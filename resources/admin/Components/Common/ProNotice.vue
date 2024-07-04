<template>
    <div class="fcal_pro_notice">
        <div>
            <h3>{{$t('UsingFreeVersion')}}</h3>
            <p>{{$t('UpgradeToPro')}}</p>
        </div>
        <a target="_blank" href="https://fluentbooking.com/pricing/?utm_source=plugin&utm_medium=wp_install&utm_campaign=ff_upgrade" class="el-button fcal_danger_btn">
            {{$t('Upgrade to Pro')}}
        </a>
    </div>
</template>
  
<script>
export default {
    name: 'ProNotice'
};
</script>
  