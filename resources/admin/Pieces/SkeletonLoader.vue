<template>
    <div v-for="i in 2" :key="i" class="fcal_calendars_skeleton">
        <el-row class="first_row">
            <el-col :span="4">
                <el-skeleton :animated="true" style="--el-skeleton-circle-size: 48px;display: flex; gap: 20px;align-items:center;">
                    <template #template>
                        <el-skeleton-item variant="image" style="flex:none;width: 48px; height: 48px;border-radius: 50%;" />
                        <el-skeleton-item />
                    </template>
                </el-skeleton>
            </el-col>

            <el-col :span="6">
                <el-skeleton :animated="true" style="display: flex; gap: 20px;align-items:center;">
                    <template #template>
                        <el-skeleton-item />
                        <el-skeleton-item />
                    </template>
                </el-skeleton>
            </el-col>
        </el-row>
        <el-row :gutter="40">
            <el-col :span="8">
                <el-skeleton :animated="true" :rows="2"/>
            </el-col>
            <el-col :span="8">
                <el-skeleton :animated="true" :rows="2"/>
            </el-col>
            <el-col :span="8">
                <el-skeleton :animated="true" :rows="2"/>
            </el-col>
        </el-row>
        
    </div>
</template>

<script>
export default {
    name: "SkeletonLoader"
}
</script>