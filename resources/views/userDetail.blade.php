@include('includes.head')
<div id="content">
    <el-descriptions direction="horizontal" :column="1" :size="size" border>
        <template #title>
            <el-button type="primary" @click="handlePrev">上一頁</el-button>
        </template>
        <el-descriptions-item label="使用者照片"><el-image style="width: 100px; height: 100px" :src="form.user_picture" :fit="fit" /></el-descriptions-item>
        <el-descriptions-item label="姓名">@{{ form.last_name }}@{{ form.first_name }}</el-descriptions-item>
        <el-descriptions-item label="email">@{{ form.email }}</el-descriptions-item>
        <el-descriptions-item label="生日">@{{ form.birthdate }}</el-descriptions-item>
        <el-descriptions-item label="Address_Type">@{{ form.address_type_name }}</el-descriptions-item>
        <el-descriptions-item label="地址">@{{ form.zipcode }}  @{{ form.city }}@{{ form.country }}@{{ form.address }}</el-descriptions-item>
        <el-descriptions-item label="地址證明文件"><el-image style="width: 100px; height: 100px" :src="form.proof" :fit="fit" /></el-descriptions-item>
    </el-descriptions>
</div>
<script>
    const {
        ElMessage
    } = ElementPlus;
    const id = "{{ $id }}";
    createApp({
        setup() {
            const form = ref({});
            const getData = () => {
                axios.post('/getDetail', {id:id})
                .then((res) => {
                    form.value = res.data;
                    console.log(res.data);
                })
            }

            const handlePrev = () => {
                window.history.back();
            }

            onMounted(() => {
                getData();
            })
            return {
                form,
                handlePrev,
            }
        }
    }).use(ElementPlus).mount('#content')
</script>