<template>
    <div>
        <el-popover
            :width="400"
            :visible="isVisible"
            :placement="placement"
            popper-class="fcrm-smartcodes-popover el-dropdown-list-wrapper">

            <div class="el_pop_data_group">
                <div class="el_pop_data_headings">
                    <ul>
                        <li
                            v-for="(item,item_index) in data"
                            :data-item_index="item_index"
                            :key="item_index"
                            :class="(activeIndex == item_index) ? 'active_item_selected' : ''"
                            @click="activeIndex = item_index">
                            {{ item.title }}
                        </li>
                    </ul>
                </div>
                <div class="el_pop_data_body">
                    <div v-for="(item,current_index) in data" :key="current_index">
                        <ul v-show="activeIndex == current_index"
                            :class="'el_pop_body_item_'+current_index">
                            <li @click="insertValue(code)" v-for="(label,code) in item.shortcodes" :key="code">
                                {{ label }}<span>{{ code }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <template #reference>
                <slot @click="visible = !visible" name="popoverButton"></slot>
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
            default: 'bottom-end'
        },
    },
    data() {
        return {
            activeIndex: 'guest'
        }
    },
    methods: {
        insertValue(code) {
            this.$emit('command', code);
        }
    },
}
</script>
