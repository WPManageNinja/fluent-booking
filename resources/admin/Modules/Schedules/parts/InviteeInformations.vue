<template>
    <div class="fcal_schedule_event_infos">
        <div class="fcal_schedule_event_infos_body">
            <div class="fcal_schedule_details_header">
                <h1 class="fcal_header_title">
                    Invitees Information
                </h1>
            </div>
            <el-table :data="spots">
                <el-table-column label="Name" width="150">
                    <template #default="scope">
                        {{ scope.row.first_name }} {{ scope.row.last_name }}
                    </template>
                </el-table-column>
                <el-table-column label="Email" width="200">
                    <template #default="scope">
                        {{ scope.row.email }}
                    </template>
                </el-table-column>
                <el-table-column label="Time Zone" width="120">
                    <template #default="scope">
                        {{ scope.row.person_time_zone }}
                    </template>
                </el-table-column>
                <el-table-column label="Booked At" width="150">
                    <template #default="scope">
                        {{ toCurrentTimezone(scope.row.created_at, 'DD MMM YYYY, hh:mma') }}
                    </template>
                </el-table-column>
                <el-table-column width="40" fixed="right">
                    <template #default="scope">
                        <el-dropdown trigger="click" popper-class="fcal_select">
                            <span class="el-dropdown-link">
                                    <el-icon><MoreFilled /></el-icon>
                            </span>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item><el-icon><Close /></el-icon> Cancel</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
import { MoreFilled, Close } from '@element-plus/icons-vue';
export default {
    name: "InviteeInformations",
    props: ['spots'],
    components: {
        MoreFilled,
        Close
    }
}
</script>