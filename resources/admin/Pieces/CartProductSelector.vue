<template>
    <div class="fcal_cart_product_selector">
        <el-select
            filterable
            clearable
            remote
            v-model="selected"
            reserve-keyword
            :placeholder="$t('Search & Select Product')"
            :remote-method="remoteFetch"
            v-loading="loading"
        >
            <el-option-group
                v-for="product in products"
                :key="product.id"
                :label="product.title">
                <el-option
                    v-for="variant in product.options"
                    :key="variant.value"
                    :label="variant.title"
                    :value="variant.value"
                >
                    <span class="fcal_select_photo" style="float: left">
                        <img :src="variant.image" :alt="variant.title" />
                        <span>{{ variant.title }}</span>
                    </span>
                    <span class="fcal_select_price">
                        <span v-html="variant.price"></span>
                    </span>
                </el-option>
            </el-option-group>
        </el-select>
        <el-popover
            :width="400"
            trigger="click"
            placement="left-start"
            ref="create_popover"
            popper-class="fcal_popover"
            @show="initCreateProduct"
        >
            <template #reference>
                <el-button class="fcal_plain_btn">
                    <el-icon><Plus /></el-icon>
                    <span>{{ $t('New') }}</span>
                </el-button>
            </template>
            <el-form v-model="newProduct" label-position="top">
                <el-form-item :label="$t('Title')">
                    <el-input v-model="newProduct.title" :placeholder="$t('Title of the product')"/>
                </el-form-item>
                <el-form-item :label="$t('Price')">
                    <el-input v-model="newProduct.price" type="number" :placeholder="$t('Price of the product')" :min="0"/>
                </el-form-item>
                <el-form-item style="margin-bottom: 0;">
                    <el-button class="fcal_primary_btn" @click="createProduct()" :loading="creatingProduct" :disabled="creatingProduct">
                        {{ $t('Create') }}
                    </el-button>
                </el-form-item>
            </el-form>
        </el-popover>
    </div>
</template>

<script>
export default {
    name: 'CartProductSelector',
    props: ['modelValue'],
    $emits: ['update:modelValue'],
    data() {
        return {
            loading: false,
            selected: this.modelValue,
            creatingProduct: false,
            products: [],
            newProduct: {
                title: '',
                price: 0
            }
        }
    },
    watch: {
        selected(value) {
            this.$emit('update:modelValue', value);
        }
    },
    methods: {
        initCreateProduct() {
            this.newProduct = {
                title: '',
                price: 0
            }
        },
        createProduct() {
            this.creatingProduct = true;
            this.$post('integrations/create/cart-product', {
                title: this.newProduct?.title,
                price: this.newProduct?.price,
            })
                .then(response => {
                    this.products.push(response.product);
                    this.selected = response.variant?.id?.toString() || '';
                    this.$refs.create_popover?.hide();
                    this.$handleSuccess(response.message);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.creatingProduct = false;
                });
        },
        remoteFetch(search = '') {
            this.loading = true;
            this.$get('integrations/options/cart-products', {
                search: search,
                include_id: this.selected
            })
                .then(response => {
                    this.products = response.items;
                })
                .catch(errors => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                })
        }
    },
    mounted() {
        this.remoteFetch();
    }
}
</script>
