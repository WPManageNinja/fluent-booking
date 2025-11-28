<template>
    <div class="fc_global_form_builder">
        <el-form @submit.prevent.native="nativeSave" :data="formData" :label-position="label_position">
            <with-label v-for="(field,fieldIndex) in fields" :key="fieldIndex"
                        :field="field">
                <component :is="field.type" v-model="formData[fieldIndex]" :field="field"/>
            </with-label>
        </el-form>
    </div>
</template>

<script>
import WithLabel from './WithLabel';
import InputText from './InputText';
import InlineCheckbox from './InlineCheckbox'
import InputOrSelect from './InputOrSelect'
import InputRadio from './InputRadio.vue'
import InputSelect from './InputOption.vue'
import WpEditorField from './WpEditorField.vue'

export default {
    name: 'global_form_builder',
    components: {
        WithLabel,
        InputText,
        InlineCheckbox,
        InputOrSelect,
        InputRadio,
        InputSelect,
        WpEditorField
    },
    $emits: ['nativeSave'],
    props: {
        formData: {
            type: Object,
            required: false,
            default() {
                return {}
            }
        },
        label_position: {
            required: false,
            type: String,
            default() {
                return 'top';
            }
        },
        fields: {
            required: true,
            type: Object
        }
    },
    methods: {
        nativeSave() {
            this.$emit('nativeSave', this.formData);
        },
        /**
         * Helper function for show/hide dependent elements
         & @return {Boolean}
         */
        compare(operand1, operator, operand2) {
            switch (operator) {
                case '=':
                    return operand1 === operand2
                case '!=':
                    return operand1 !== operand2
            }
        },

        /**
         * Checks if a prop is dependent on another
         * @param listItem
         * @return {boolean}
         */
        dependencyPass(listItem) {
            if (listItem.dependency) {
                const optionPaths = listItem.dependency.depends_on.split('/');

                const dependencyVal = optionPaths.reduce((obj, prop) => {
                    return obj[prop]
                }, this.formData);

                if (this.compare(listItem.dependency.value, listItem.dependency.operator, dependencyVal)) {
                    return true;
                }
                return false;
            }
            return true;
        }
    }
}
</script>
