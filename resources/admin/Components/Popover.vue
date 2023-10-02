<template>
    <div>
        <el-popover
            :width="200"
            :placement="placement"
            popper-class="fcal_popover fcal_select el-dropdown-list-wrapper"
            trigger="click">
            <ul class="el-dropdown-menu el-dropdown-list">
                <ul>
                    <span class="group-title"> {{ groupTitle }} </span>
                    <li v-for="(title, code, index) in data"
                        :key="index"
                        @click="insertValue(code)"
                        class="el-dropdown-menu__item">
                        {{ title }}
                    </li>
                </ul>
            </ul>
            <template #reference>
                <slot name="popoverButton"></slot>
            </template>
        </el-popover>
    </div>
</template>

<script>
export default {
    name: 'popoverDropdown',
    props: {
        data: Object,
        isVisible: Boolean,
        groupTitle: String,
        placement: {
            type: String,
            default: 'right-start'
        },
    },
    methods: {
        insertValue(code) {
            this.$emit('command', code);
        }
    },
}
</script>
