<template>
    <div class="fcal_schedule_profile_box">
        <div class="fcal_schedule_profile_header">
            <h1>FluentCrm Profile</h1>
        </div>
        <div class="fcal_schedule_profile_body">
            <div class="fcal_schedule_profile_avatar">
                <img src="https://www.gravatar.com/avatar/569b8d33c316ed729190904c4bef26e6?s=128&d=https%3A%2F%2Fui-avatars.com%2Fapi%2FTanbir%2BAhmod/128" alt="">
            </div>
            <div class="fcal_schedule_profile_info">
                <div class="fcal_schedule_profile_title">
                    <h1><a href="#">Tanbir Ahmod</a></h1>
                    <p>subscribed</p>
                </div>
                <div class="fcal_schedule_profile_tag_lists">
                    <div class="fcal_schedule_profile_stats">
                        <span>Emails: <b>80</b></span>
                        <span>Opens: <b>50</b></span>
                        <span>Clicks: <b>20</b></span>
                    </div>
                    <div class="fcal_schedule_profile_tags">
                        <el-icon><PriceTag /></el-icon>
                        <span>Tag 1</span>
                        <span>Tag 2</span>
                        <span>Tag 3</span>
                        <span>Tag 4</span>
                        <span>Fast delivery with Express Shipping, arrives inxdvdv dsvredq</span>
                    </div>
                </div>

                <div class="fcal_schedule_profile_footer">
                    <a href="" class="fcal_primary_btn">
                        View CRM Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { PriceTag } from '@element-plus/icons-vue';

export default {
    name: "ScheduleProfile",
    components: {
        PriceTag
    }
}
</script>

<style scoped>

</style>