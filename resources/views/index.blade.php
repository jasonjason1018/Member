@include('includes.head')
<div id="content">
    <el-table :data="form" border>
        <el-table-column label="編號" prop="id" align="center"></el-table-column>
        <el-table-column label="姓名" align="center">
            <template #default="scope">
                @{{ scope.row.last_name }}@{{ scope.row.first_name }}
            </template>
        </el-table-column>
        <el-table-column label="信箱" prop="email" align="center"></el-table-column>
        <el-table-column align="center">
            <template #header>
                <el-button type="primary" @click="exportFile">匯出</el-button><el-button type="primary" @click="register">註冊</el-button>
            </template>
            <template #default="scope">
                    <el-button type="success" @click="goToDetail(scope.row.id)">詳細</el-button>
                    <el-button type="primary" @click="goToEdit(scope.row.id)">編輯</el-button>
                    <el-button type="danger" @click="dataDelete(scope.row.id)">刪除</el-button>
            </template>
        </el-table-column>
    </el-table>
    <div class="container">
        <el-pagination v-if="form" layout="prev, pager, next" @change="pagination" :default-page-size="page_size" :total="page_total" />
    </div>
    <el-dialog v-model="centerDialogVisible" :title="確認刪除" align-center>
        <center>刪除後將無法恢復，確定刪除?</center>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="centerDialogVisible = false">取消</el-button>
                <el-button type="primary" @click="handleDelete">
                    確認
                </el-button>
            </span>
        </template>
    </el-dialog>
</div>
<script>
     createApp({
        setup() {
            const form = ref([]);
            const data = ref();
            const centerDialogVisible = ref(false);
            const dialog = ref({});
            const page_size = ref(1);
            const page_total = ref();
            const register = () => {
                window.location.href="/register";
            }

            const getUserList = () => {
                axios.get('/getUserList')
                .then((res) => {
                    data.value = res.data;
                    page_total.value = res.data.length;
                })
                .finally(() => {
                    pagination();
                })
            }

            const pagination = (value) => {
                form.value = [];
                value = value == undefined?1:value;
                for(i=value-1;i<value+page_size.value-1;i++){
                    form.value.push(data.value[i]);
                }
            }

            const goToDetail = (id) => {
                window.location.href="/userDetail/"+id;
            }

            const goToEdit = (id) => {
                window.location.href="/register/"+id;
            }

            const exportFile = () => {
                window.location.href="/exportExcel";
            }

            const dataDelete = (id) => {
                centerDialogVisible.value = true;
                dialog.value.id = id;
            }

            const handleDelete = () => {
                axios.post('/userDelete', dialog.value)
                .then((res) => {
                    console.log(res.data);
                    getUserList();
                })
                .finally(() => {
                    centerDialogVisible.value = false;
                })
            }

            onMounted(() => {
                getUserList();
            })

            return {
                register,
                form,
                goToDetail,
                goToEdit,
                exportFile,
                dataDelete,
                handleDelete,
                centerDialogVisible,
                page_size,
                page_total,
                pagination,
            }
        }
    }).use(ElementPlus).mount('#content')
</script>
<style>
    .container {
        display: grid;
        place-items: center;
    }
</style>