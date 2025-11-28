<template>
    <div class="fcal_pro_notice">
        <div>
            <h3>{{$t('UnlockWithPro')}}</h3>
            <p>{{$t('UpgradeToPro')}}</p>
        </div>
        <a target="_blank" :href="appVars.upgrade_url" class="el-button fcal_danger_btn">
            {{$t('Upgrade to Pro')}}
        </a>
    </div>
</template>
  
<script>
export default {
    name: 'ProNotice'
};
</script>