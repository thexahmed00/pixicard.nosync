import{g as he,p as Oe,k as ue,q as R,r as i,a as se,s as pe,v as Se,x as _e,j as me,w as U,h as O,o as a,c as f,d as e,y as C,n as L,e as l,f as w,t as n,F as ce,z as T,A as xe,T as ve,i as oe,B as ye,C as be,l as ge,D as je,m as fe,E as Me,G as qe,u as W,b as ee}from"./index-C5hIcM1_.js";import{G as Fe,O as Be}from"./drawer-CJQap9CO.js";import{C as Te}from"./customers-BemZ2GVg.js";import{O as De}from"./orders-B63cPcaD.js";import{C as Le}from"./products-Cg4BVBYT.js";import{a as we,_ as Ne}from"./FlashMessage-Ca8WSK6k.js";import"./window-BcH-t1wI.js";const Ue=he`
    query getOutletProducts ($page: Int!, $first: Int!) {
        getOutletProducts (page: $page, first: $first) {
            paginatorInfo {
                count
                currentPage
                lastPage
                total
            }
            data {
                id
                sku
                type
                name
                quantity
                price
                convertedPrice
                barcode
                priceHtml
                images {
                    id
                    url
                }
                categories {
                    id
                    name
                    parentId
                    children {
                        id
                        name
                        parentId
                    }
                }
                variantConfigurations
                downloadableSamples {
                    id
                    url
                    file
                    fileName
                    fileUrl
                    type
                    sortOrder
                    productId
                    createdAt
                    updatedAt
                    translations {
                        id
                        locale
                        title
                        productDownloadableSampleId
                    }
                }
                downloadableLinks {
                    id
                    title
                    price
                    url
                    file
                    fileUrl
                    fileName
                    type
                    sampleUrl
                    sampleFile
                    sampleFileName
                    sampleFileUrl
                    sampleType
                    sortOrder
                    productId
                    downloads
                    translations {
                        id
                        locale
                        title
                        productDownloadableLinkId
                    }
                }
                groupedProducts {
                    id
                    qty
                    sortOrder
                    productId
                    associatedProductId
                    associatedProduct {
                        id
                        type
                        name
                        attributeFamilyId
                        sku
                        parentId
                        isSaleable
                        priceHtml {
                            minPrice
                            priceHtml
                        }
                    }
                }
                options {
                    id
                    label
                    type
                    isRequired
                    sortOrder
                    products {
                        id
                        qty
                        name
                        productId
                        isDefault
                        sortOrder
                        inStock
                        inventory
                        price {
                            regular {
                                price
                                formattedPrice
                            }
                            final {
                                price
                                formattedPrice
                            }
                        }
                    }
                }
            }
        }
    }
`,Ee=he`
    query getOutletCategories ($page: Int!, $first: Int!) {
        getOutletCategories (page: $page, first: $first) {
            paginatorInfo {
                count
                currentPage
                lastPage
                total
            }
            data {
                id
                slug
                name
                parentId
                children {
                    id
                    slug
                    name
                    parentId
                    children {
                        id
                        slug
                        name
                        parentId
                    }
                }
            }
        }
    }
`,Ae={class:"sticky top-0 z-[10001] bg-white shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]"},ze={class:"hidden items-center justify-between p-2.5 xl:flex"},Re={class:"flex items-center gap-1.5"},Ge={key:0,class:"ml-10 flex items-center gap-x-2"},Qe={class:"relative flex w-[486px] max-w-[486px] items-center text-dark max-lg:w-[400px] ltr:ml-2.5 rtl:mr-2.5"},He=["placeholder"],We={class:"flex items-center gap-x-2"},Je={class:"text-base font-semibold leading-5"},Ke={key:0,class:"flex items-center gap-2.5 p-2 xl:hidden"},Ve={class:"relative flex w-full items-center text-dark"},Xe=["placeholder"],Ye={key:1,class:"text-xl font-medium leading-6"},Ze={key:0,class:"fixed bottom-0 left-0 right-0 top-0 z-[10002] w-full bg-white"},et={class:"flex h-full flex-col justify-between bg-extraLight"},tt={class:"flex flex-col gap-y-1"},st={class:"flex h-16 justify-between gap-2 px-4 py-5"},ot={class:"text-xl font-semibold leading-6 text-dark"},rt={class:"flex items-center gap-x-6"},at={class:"grid grid-cols-3 gap-2.5 p-4 lg:grid-cols-4"},lt={class:"text-center text-[12px] font-medium leading-4"},nt={class:"flex justify-between gap-2.5 border-t bg-white px-4"},it={class:"flex items-center justify-center gap-2.5"},dt=["src"],ct={key:1,src:be,class:"h-10 w-10 cursor-pointer rounded-full",alt:"profile image"},ut={class:"flex flex-col gap-1"},pt={class:"text-base font-semibold leading-5"},mt={class:"text-[12px] font-medium leading-4"},gt={class:"text-[12px] font-medium leading-4"},ft={class:"grid gap-1"},ht={class:"flex justify-end gap-6"},_t=["onClick"],xt={type:"submit",class:"primary-button w-36"},vt=["onSubmit"],yt={class:"flex flex-col gap-1"},bt={for:"name",class:"text-base font-semibold leading-5 text-dark"},wt={key:0,class:"flex flex-col gap-1"},kt={for:"sku",class:"text-base font-semibold leading-5 text-dark"},$t={class:"flex flex-col gap-1"},Ct={for:"price",class:"text-base font-semibold leading-5 text-dark"},Pt={class:"flex gap-2.5 max-sm:flex-col max-sm:gap-4"},It={class:"flex flex-col gap-1"},Ot={for:"quantity",class:"text-base font-semibold leading-5 text-dark"},St={class:"flex flex-col gap-1"},jt={for:"weight",class:"text-base font-semibold leading-5 text-dark"},Mt={class:"flex justify-end gap-6 max-sm:justify-between"},qt=["onClick"],Ft={type:"submit",class:"primary-button w-36 max-sm:w-full"},Bt={__name:"Header",setup(N){const{t:$}=Oe(),x=ge(),v=ue("emitter"),u=fe(),{appContext:h}=je(),S=R(()=>h.config.globalProperties.$isOnline.value),k=i(!1),m=o=>{let t=o.target.value;v.emit("search_product",t)},d=i(null),{mutate:I}=se(Le),E=(o,{setErrors:t,resetForm:p})=>{if(!S.value){v.emit("add_flash",{type:"error",message:$("pos.common.flash_messages.offline_error")}),d.value.toggle();return}I({input:{name:o.name,sku:o.sku||"",price:parseFloat(o.price),quantity:parseInt(o.quantity),weight:parseFloat(o.weight)}}).then(y=>{const{createOutletProduct:c}=y==null?void 0:y.data;c!=null&&c.success?(v.emit("add_flash",{type:"success",message:c==null?void 0:c.message}),p(),d.value.toggle(),v.emit("add_to_cart",{productId:c.product.id,quantity:1})):c!=null&&c.errors&&t(JSON.parse(c.errors))})},j=i(!1),q=i(!1);pe(()=>{x.getItem("configurations",1).then(o=>{var t,p,y,c;j.value=((p=(t=o.configurations)==null?void 0:t.find(D=>D.code=="pos.settings.barcode.hide"))==null?void 0:p.value)!="1",q.value=((c=(y=o.configurations)==null?void 0:y.find(D=>D.code=="pos.settings.product.allow_sku"))==null?void 0:c.value)=="1"})});const G=i(null),re=o=>{let t=o.barcode;const p=V();p&&!t.startsWith(p)&&(t=p+t),v.emit("search_barcode",t),G.value.toggle()},J=()=>{h.config.globalProperties.$isOnline.value=!S.value};Se(()=>{h.config.globalProperties.$removeNetworkListeners()});const Q=()=>{u.remove("products_fetched"),u.remove("customers_fetched"),u.remove("orders_fetched"),u.remove("categories_fetched"),window.location.reload()},ae=i([{name:"home",path:"/home",icon:"icon-home"},{name:"customers",path:"/customers",icon:"icon-customer"},{name:"cashier",path:"/cashier",icon:"icon-cashier"},{name:"orders",path:"/orders",icon:"icon-cart"},{name:"products",path:"/products",icon:"icon-product"},{name:"reports",path:"/reports",icon:"icon-reports"},{name:"settings",path:"/settings",icon:"icon-settings"}]),M=i({});_e(async()=>{const o=await x.getAllItems("agents");M.value=o[0]});const{mutate:K}=se(we),le=me(),ne=async()=>{var o,t;try{const p=await K();(t=(o=p==null?void 0:p.data)==null?void 0:o.agentLogout)!=null&&t.success&&(localStorage.removeItem("accessToken"),u.remove("products_fetched"),u.remove("customers_fetched"),u.remove("orders_fetched"),u.remove("categories_fetched"),le.push({path:"/"}))}catch{}};U(k,o=>{o?document.body.style.overflow="hidden":document.body.style.overflow="auto"});const V=()=>{x.getItem("configurations",1).then(o=>{var t,p;return(p=(t=o.configurations)==null?void 0:t.find(y=>y.code=="pos.settings.barcode.prefix"))==null?void 0:p.value})};return(o,t)=>{const p=O("router-link"),y=O("v-field"),c=O("v-error-message"),D=O("v-form"),X=O("modal");return a(),f("header",Ae,[e("section",ze,[e("div",Re,[t[15]||(t[15]=e("h1",{class:"text-2xl font-semibold text-dark"}," Pixicard POS ",-1)),o.$route.path.split("/")[1]=="home"?(a(),f("div",Ge,[e("div",Qe,[e("input",{type:"text",class:"h-12 w-full rounded-md bg-light px-3.5 py-3 text-base font-normal leading-5 text-dark",placeholder:o.$t("pos.layout.header.search_products"),onInput:m},null,40,He),t[12]||(t[12]=e("i",{class:"icon-search absolute top-3 flex items-center text-2xl ltr:right-2 rtl:left-2"},null,-1))]),j.value?(a(),f("div",{key:0,class:"flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light",onClick:t[0]||(t[0]=b=>o.$refs.barcodeModal.open())},t[13]||(t[13]=[e("i",{class:"icon-barcode text-2xl text-dark"},null,-1)]))):C("",!0),e("div",{class:"flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light",onClick:t[1]||(t[1]=b=>o.$refs.productCreateModal.open())},t[14]||(t[14]=[e("i",{class:"icon-box text-2xl text-dark"},null,-1)]))])):C("",!0)]),e("div",We,[e("div",{class:"flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light",onClick:t[2]||(t[2]=b=>Q())},t[16]||(t[16]=[e("i",{class:"icon-sync text-2xl text-dark"},null,-1)])),e("div",{class:"flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light",onClick:t[3]||(t[3]=b=>J())},[e("i",{class:L([[S.value?"text-limeGreen":"text-red-500"],"icon-network text-2xl"])},null,2)]),l(p,{to:"/orders",class:"transparent-button"},{default:w(()=>[t[17]||(t[17]=e("i",{class:"icon-hold-cart text-2xl"},null,-1)),e("span",Je,n(o.$t("pos.layout.header.hold_orders")),1)]),_:1})])]),o.$route.path.split("/")[1]!="payment"?(a(),f("section",Ke,[e("div",{class:"flex h-12 min-w-12 cursor-pointer items-center justify-center rounded-lg bg-light",onClick:t[4]||(t[4]=b=>k.value=!k.value)},t[18]||(t[18]=[e("span",{class:"icon-menu text-2xl"},null,-1)])),o.$route.path.split("/")[1]=="home"?(a(),f(ce,{key:0},[e("div",Ve,[e("input",{type:"text",class:"h-12 w-full rounded-md bg-light px-3.5 py-3 text-base font-normal leading-5 text-dark",placeholder:o.$t("pos.layout.header.search_products"),onInput:m},null,40,Xe),t[19]||(t[19]=e("i",{class:"icon-search absolute top-3 flex items-center text-2xl ltr:right-2 rtl:left-2"},null,-1))]),j.value?(a(),f("div",{key:0,class:"flex h-12 min-w-12 cursor-pointer items-center justify-center rounded-md bg-light",onClick:t[5]||(t[5]=b=>o.$refs.barcodeModal.open())},t[20]||(t[20]=[e("i",{class:"icon-barcode text-2xl text-dark"},null,-1)]))):C("",!0),e("div",{class:"flex h-12 min-w-12 cursor-pointer items-center justify-center rounded-md bg-light",onClick:t[6]||(t[6]=b=>o.$refs.productCreateModal.open())},t[21]||(t[21]=[e("i",{class:"icon-box text-2xl text-dark"},null,-1)]))],64)):(a(),f("p",Ye,n(o.$t(`pos.layout.sidebar.${o.$route.path.split("/")[1]}`)),1))])):C("",!0),(a(),T(ye,{to:"body"},[l(ve,{"enter-active-class":"transition ease-out duration-300","enter-from-class":"opacity-0 transform -translate-x-full","enter-to-class":"opacity-100 transform translate-x-0","leave-active-class":"transition ease-in duration-300","leave-from-class":"opacity-100 transform translate-x-0","leave-to-class":"opacity-0 transform -translate-x-full"},{default:w(()=>{var b,A,z,Y;return[k.value?(a(),f("div",Ze,[e("div",et,[e("div",tt,[e("div",st,[e("h1",ot,n(o.$t("pos.layout.header.title")),1),e("div",rt,[e("div",{class:"flex cursor-pointer items-center justify-center",onClick:t[7]||(t[7]=F=>Q())},t[22]||(t[22]=[e("i",{class:"icon-sync text-2xl text-dark"},null,-1)])),e("div",{class:"flex cursor-pointer items-center justify-center",onClick:t[8]||(t[8]=F=>J())},[e("i",{class:L([[S.value?"text-limeGreen":"text-red-500"],"icon-network text-2xl"])},null,2)]),e("div",{class:"flex cursor-pointer items-center justify-center",onClick:t[9]||(t[9]=F=>k.value=!k.value)},t[23]||(t[23]=[e("span",{class:"icon-cross rounded-full border-2 border-dark text-lg leading-6"},null,-1)]))])]),e("div",at,[(a(!0),f(ce,null,xe(ae.value,(F,ie)=>(a(),T(p,{key:ie,to:F.path,class:L([[o.$route.path.split("/")[1]===F.name?"text-primary border-2 border-primary bg-light":""],"grid aspect-square cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary"]),onClick:t[10]||(t[10]=Z=>k.value=!k.value)},{default:w(()=>[e("i",{class:L(["icon text-2xl",F.icon])},null,2),e("span",lt,n(o.$t(`pos.layout.sidebar.${F.name}`)),1)]),_:2},1032,["to","class"]))),128))])]),e("div",nt,[e("div",it,[(b=M.value)!=null&&b.imageUrl?(a(),f("img",{key:0,src:M.value.imageUrl,class:"h-10 w-10 cursor-pointer rounded-full",alt:"profile image"},null,8,dt)):(a(),f("img",ct)),e("div",ut,[e("span",pt,n(`${(A=M.value)==null?void 0:A.firstName} ${(z=M.value)==null?void 0:z.lastName}`),1),e("span",mt,n((Y=M.value)==null?void 0:Y.email),1)])]),e("div",{class:"grid h-20 w-20 cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary",onClick:t[11]||(t[11]=F=>ne())},[t[24]||(t[24]=e("i",{class:"icon icon-logout text-2xl"},null,-1)),e("span",gt,n(o.$t("pos.layout.sidebar.logout")),1)])])])])):C("",!0)]}),_:1}),l(X,{ref_key:"barcodeModal",ref:G},{header:w(()=>[oe(n(o.$t("pos.layout.header.barcode_form.title")),1)]),content:w(({toggle:b})=>[l(D,{class:"grid gap-4",onSubmit:re},{default:w(()=>[e("div",ft,[l(y,{type:"text",name:"barcode",id:"barcode",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required",placeholder:o.$t("pos.layout.header.barcode_form.barcode_placeholder")},null,8,["placeholder"]),l(c,{name:"barcode",class:"text-sm text-red-500"})]),e("div",ht,[e("button",{type:"button",class:"transparent-button w-36",onClick:b},n(o.$t("pos.layout.header.barcode_form.cancel_btn_title")),9,_t),e("button",xt,n(o.$t("pos.layout.header.barcode_form.proceed_btn_title")),1)])]),_:2},1024)]),_:1},512),l(X,{ref_key:"productCreateModal",ref:d},{header:w(()=>[oe(n(o.$t("pos.layout.header.product_create_form.title")),1)]),content:w(({toggle:b})=>[l(D,null,{default:w(({handleSubmit:A})=>[e("form",{class:"flex flex-col justify-start gap-4",onSubmit:z=>A(z,E)},[e("div",yt,[e("label",bt,n(o.$t("pos.layout.header.product_create_form.name")),1),l(y,{type:"text",name:"name",id:"name",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required"}),l(c,{name:"name",class:"text-sm text-red-500"})]),e("div",{class:L([q.value?"flex max-sm:flex-col max-sm:gap-4 gap-2.5":""])},[q.value?(a(),f("div",wt,[e("label",kt,n(o.$t("pos.layout.header.product_create_form.sku")),1),l(y,{type:"text",name:"sku",id:"sku",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required"}),l(c,{name:"sku",class:"text-sm text-red-500"})])):C("",!0),e("div",$t,[e("label",Ct,n(o.$t("pos.layout.header.product_create_form.price")),1),l(y,{type:"text",name:"price",id:"price",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required|decimal"}),l(c,{name:"price",class:"text-sm text-red-500"})])],2),e("div",Pt,[e("div",It,[e("label",Ot,n(o.$t("pos.layout.header.product_create_form.quantity")),1),l(y,{type:"text",name:"quantity",id:"quantity",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required|numeric"}),l(c,{name:"quantity",class:"text-sm text-red-500"})]),e("div",St,[e("label",jt,n(o.$t("pos.layout.header.product_create_form.weight")),1),l(y,{type:"text",name:"weight",id:"weight",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required|numeric"}),l(c,{name:"weight",class:"text-sm text-red-500"})])]),e("div",Mt,[e("button",{type:"button",class:"transparent-button w-36 max-sm:w-full",onClick:b},n(o.$t("pos.layout.header.product_create_form.cancel_btn_title")),9,qt),e("button",Ft,n(o.$t("pos.layout.header.product_create_form.proceed_btn_title")),1)])],40,vt)]),_:2},1024)]),_:1},512)]))])}}},Tt={class:"fixed top-16 z-[1000] hidden h-full bg-white p-2 shadow-[-1px_0px_0px_0px_rgba(0,0,0,0.1)_inset] xl:block"},Dt={class:"journal-scroll h-[calc(80vh-100px)] overflow-auto"},Lt={class:"grid w-full"},Nt={class:"text-center text-[12px] font-medium leading-4"},Ut={class:"mt-4 grid items-center gap-1"},Et={class:"flex items-center justify-center"},At=["src"],zt={key:1,src:be,class:"h-10 w-10 cursor-pointer rounded-full",alt:"profile image"},Rt={class:"text-[12px] font-medium leading-4"},Gt={__name:"Sidebar",setup(N){const $=ge(),x=fe(),v=i({});_e(async()=>{const m=await $.getAllItems("agents");v.value=m[0]});const{mutate:u}=se(we),h=me(),S=async()=>{var m,d;try{const I=await u();(d=(m=I==null?void 0:I.data)==null?void 0:m.agentLogout)!=null&&d.success&&(localStorage.removeItem("accessToken"),x.remove("products_fetched"),x.remove("customers_fetched"),x.remove("orders_fetched"),x.remove("categories_fetched"),h.push({path:"/"}))}catch{}},k=i([{name:"home",path:"/home",icon:"icon-home"},{name:"customers",path:"/customers",icon:"icon-customer"},{name:"cashier",path:"/cashier",icon:"icon-cashier"},{name:"orders",path:"/orders",icon:"icon-cart"},{name:"products",path:"/products",icon:"icon-product"},{name:"reports",path:"/reports",icon:"icon-reports"},{name:"settings",path:"/settings",icon:"icon-settings"}]);return(m,d)=>{var E;const I=O("router-link");return a(),f("div",Tt,[e("div",Dt,[e("nav",Lt,[(a(!0),f(ce,null,xe(k.value,(j,q)=>(a(),T(I,{key:q,to:j.path,class:L([[m.$route.path.split("/")[1]===j.name?"text-primary border-2 border-primary bg-light":""],"grid h-20 w-20 cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary"])},{default:w(()=>[e("i",{class:L(["icon text-2xl",j.icon])},null,2),e("span",Nt,n(m.$t(`pos.layout.sidebar.${j.name}`)),1)]),_:2},1032,["to","class"]))),128))])]),e("div",Ut,[e("div",Et,[(E=v.value)!=null&&E.imageUrl?(a(),f("img",{key:0,src:v.value.imageUrl,class:"h-10 w-10 cursor-pointer rounded-full",alt:"profile image"},null,8,At)):(a(),f("img",zt))]),e("div",{class:"grid h-20 w-20 cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary",onClick:d[0]||(d[0]=j=>S())},[d[1]||(d[1]=e("i",{class:"icon icon-logout text-2xl"},null,-1)),e("span",Rt,n(m.$t("pos.layout.sidebar.logout")),1)])])])}}},Qt={key:0,class:"fixed inset-0 z-[10003] overflow-y-auto bg-[#0003] backdrop-blur-xs"},Ht={class:"flex min-h-full items-end justify-center p-4"},Wt={class:"absolute top-1/2 z-[999] w-full max-w-[576px] -translate-y-1/2 rounded-2xl bg-white max-sm:w-[96%]"},Jt={class:"grid gap-4 p-6"},Kt={class:"flex items-center gap-2.5"},Vt={class:"text-2xl font-semibold text-dark"},Xt={class:"pb-2.5 text-base font-normal text-dark"},Yt={class:"flex justify-end gap-6"},Zt={__name:"ConfirmModal",setup(N){const $=i(!1),x=i(null),v=i(null),u=ue("emitter"),h=({agree:m=()=>{},disagree:d=()=>{}})=>{$.value=!0,x.value=m,v.value=d},S=()=>{$.value=!1,v.value()},k=()=>{$.value=!1,x.value()};return pe(()=>{u.on("open_confirm_modal",h)}),Me(()=>{u.off("open_confirm_modal")}),(m,d)=>(a(),T(ve,{"enter-active-class":"transition ease-out duration-300","enter-from-class":"opacity-0 transform scale-90","enter-to-class":"opacity-100 transform scale-100","leave-active-class":"transition ease-in duration-300","leave-from-class":"opacity-100 transform scale-100","leave-to-class":"opacity-0 transform scale-90"},{default:w(()=>[$.value?(a(),f("div",Qt,[e("div",Ht,[e("div",Wt,[e("div",Jt,[e("div",Kt,[d[2]||(d[2]=e("span",{class:"icon-warning cursor-pointer text-2xl text-dark"},null,-1)),e("p",Vt,n(m.$t("pos.common.confirm_modal.title")),1)]),e("p",Xt,n(m.$t("pos.common.confirm_modal.description")),1),e("div",Yt,[e("button",{type:"button",class:"transparent-button w-36",onClick:d[0]||(d[0]=I=>S())},n(m.$t("pos.common.confirm_modal.disagree_btn_title")),1),e("button",{type:"submit",class:"primary-button w-36",onClick:d[1]||(d[1]=I=>k())},n(m.$t("pos.common.confirm_modal.agree_btn_title")),1)])])])])])):C("",!0)]),_:1}))}},es={class:"flex flex-col gap-y-1.5"},ts={key:0,class:"text-lg font-semibold"},ss={class:"flex items-center gap-x-3 whitespace-nowrap"},os={class:"flex h-2 w-full overflow-hidden rounded-full bg-gray-200",role:"progressbar","aria-valuenow":"100","aria-valuemin":"0","aria-valuemax":"100"},rs={class:"text-end"},te={__name:"ProgressBar",props:{progress:{type:Number,required:!0},name:{type:String,required:!1}},setup(N){return($,x)=>(a(),f("div",es,[N.name?(a(),f("p",ts,n(N.name),1)):C("",!0),e("div",ss,[e("div",os,[e("div",{class:"flex flex-col justify-center overflow-hidden whitespace-nowrap rounded-full bg-blue-600 text-center text-xs text-white transition duration-500",style:qe(`width: ${N.progress}%`)},null,4)]),e("div",rs,[e("span",{class:L(["ms-auto flex size-4 shrink-0 items-center justify-center rounded-full text-white",[N.progress===100?"bg-green-500":"bg-greyish"]])},x[0]||(x[0]=[e("svg",{class:"size-3 shrink-0",xmlns:"http://www.w3.org/2000/svg",width:"24",height:"24",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor","stroke-width":"2","stroke-linecap":"round","stroke-linejoin":"round"},[e("polyline",{points:"20 6 9 17 4 12"})],-1)]),2)])])]))}},as={class:"flex min-h-[calc(100vh)] gap-4 bg-white"},ls={class:"flex justify-between gap-2.5"},ns=["onClick"],is={class:"flex flex-col"},ds={class:"text-base"},cs={class:"my-5 flex flex-col gap-5 pt-5"},us={class:"flex justify-between gap-2.5"},ps=["onClick"],ms=["onSubmit"],gs={class:"grid gap-1"},fs={for:"opening_amount",class:"text-base font-semibold leading-5 text-dark"},hs={class:"grid gap-1"},_s={for:"remarks",class:"text-base font-semibold leading-5 text-dark"},Cs={__name:"Main",setup(N){const $=ue("emitter"),x=me(),v=ge(),u=fe(),h=i(null),S=i(u.get("products_fetched")),k=i(u.get("customers_fetched")),m=i(u.get("orders_fetched")),d=i(u.get("categories_fetched")),{result:I,loading:E,fetchMore:j}=W(Ue,{page:1,first:20},()=>({enabled:!S.value})),q=i([]),G=i(0),re=R(()=>{var s;return((s=I.value)==null?void 0:s.getOutletProducts)??{}});U([re],async([s])=>{q.value=s.data??[];const r=s.paginatorInfo??{};r.currentPage===1&&!h.isOpen&&h.value.open(),$.emit("products_fetched",q.value),G.value=Math.ceil(r.currentPage/r.lastPage*100),r.currentPage<r.lastPage?await j({variables:{page:r.currentPage+1},updateQuery:(P,{fetchMoreResult:_})=>{const g=_.getOutletProducts.data,B=[...P.getOutletProducts.data,...g];return{getOutletProducts:{..._.getOutletProducts,data:B}}}}):q.value.length===r.total&&(await v.updateItem("products",{id:"all",products:q.value}),u.set("products_fetched",!0))},{deep:!0});const{result:J,loading:Q,fetchMore:ae}=W(Ee,{page:1,first:20},()=>({enabled:!d.value})),M=i([]),K=i(0),le=R(()=>{var s;return((s=J.value)==null?void 0:s.getOutletCategories)??{}});U([le],async([s])=>{M.value=s.data??[];const r=s.paginatorInfo??{};r.currentPage===1&&!h.isOpen&&h.value.open(),$.emit("categories_fetched",M.value),K.value=Math.ceil(r.currentPage/r.lastPage*100),r.currentPage<r.lastPage?await ae({variables:{page:r.currentPage+1},updateQuery:(P,{fetchMoreResult:_})=>{const g=_.getOutletCategories.data,B=[...P.getOutletCategories.data,...g];return{getOutletCategories:{..._.getOutletCategories,data:B}}}}):M.value.length===r.total&&(await v.updateItem("categories",{id:"all",categories:M.value}),u.set("categories_fetched",!0))},{deep:!0});const{result:ne,loading:V,fetchMore:o}=W(Te,{page:1,first:20},()=>({enabled:!k.value})),t=i([]),p=i(0),y=R(()=>{var s;return((s=ne.value)==null?void 0:s.getCustomers)??{}});U([y],async([s])=>{t.value=s.data??[];const r=s.paginatorInfo??{};r.currentPage===1&&!h.isOpen&&h.value.open(),p.value=Math.ceil(r.currentPage/r.lastPage*100),r.currentPage<r.lastPage?await o({variables:{page:r.currentPage+1},updateQuery:(P,{fetchMoreResult:_})=>{const g=_.getCustomers.data,B=[...P.getCustomers.data,...g];return{getCustomers:{..._.getCustomers,data:B}}}}):t.value.length===r.total&&(await v.updateItem("customers",{id:"all",customers:t.value}),u.set("customers_fetched",!0))},{deep:!0});const{result:c,loading:D,fetchMore:X}=W(De,{page:1,first:20},()=>({enabled:!m.value})),b=i([]),A=i(0),z=R(()=>{var s;return((s=c.value)==null?void 0:s.getOrders)??{}});U([z],async([s])=>{b.value=s.data??[];const r=s.paginatorInfo??{};r.currentPage===1&&!h.isOpen&&h.value.open(),A.value=Math.ceil(r.currentPage/r.lastPage*100),r.currentPage<r.lastPage?await X({variables:{page:r.currentPage+1},updateQuery:(P,{fetchMoreResult:_})=>{const g=_.getOrders.data,B=[...P.getOrders.data,...g];return{getOrders:{..._.getOrders,data:B}}}}):b.value.length===r.total&&(await v.updateItem("orders",{id:"all",orders:b.value}),u.set("orders_fetched",!0))},{deep:!0});const{result:Y,error:F}=W(Fe),ie=R(()=>{var s;return((s=Y.value)==null?void 0:s.getDrawerDetails)??[]});U(F,s=>{s.message==="Unauthenticated."?(localStorage.removeItem("accessToken"),x.push({path:"/"})):s.message==="status_off"&&(localStorage.removeItem("accessToken"),x.push({path:"/404"}))}),U(ie,s=>{s.openingAmount==null&&Z.value.toggle()});const Z=i(null),de=i(!1),{mutate:ke}=se(Be),$e=(s,{setErrors:r,resetForm:P})=>{de.value=!0,ke({input:{openingAmount:parseFloat(s.opening_amount),remark:s.remark}}).then(_=>{const g=_.data.openDrawer;g!=null&&g.success?(Z.value.toggle(),P(),$.emit("add_flash",{type:"success",message:g==null?void 0:g.message})):g!=null&&g.errors&&r(JSON.parse(g.errors)),de.value=!1})};return U([E,Q,V,D],([s,r,P,_])=>{!s&&!r&&!P&&!_&&h.value.close()}),pe(()=>{const s=localStorage.getItem("loginMessage");s&&($.emit("add_flash",{type:"success",message:s}),localStorage.removeItem("loginMessage"))}),(s,r)=>{const P=O("router-view"),_=O("modal"),g=O("v-field"),B=O("v-error-message"),Ce=O("Button"),Pe=O("v-form");return a(),f("div",null,[l(Bt),l(Ne),l(Zt),e("div",as,[s.$route.path.split("/")[1]!="payment"?(a(),T(Gt,{key:0})):C("",!0),e("div",{class:L([s.$route.path.split("/")[1]=="payment"?"p-4":"pl-4 pr-4 xl:ltr:pl-[120px] xl:rtl:pr-[120px]","max-w-full flex-1 transition-all duration-300 xl:bg-light"])},[l(P)],2)]),(a(),T(ye,{to:"body"},[l(_,{ref_key:"progressModal",ref:h},{header:w(({toggle:H})=>[e("div",ls,[oe(n(s.$t("pos.layout.main.progress_modal.title"))+" ",1),!ee(E)&&!ee(Q)&&!ee(V)&&!ee(D)?(a(),f("span",{key:0,class:"icon-cross cursor-pointer text-2xl text-dark hover:text-primary",onClick:H},null,8,ns)):C("",!0)])]),content:w(()=>[e("div",is,[e("p",ds,n(s.$t("pos.layout.main.progress_modal.description")),1),e("div",cs,[S.value?C("",!0):(a(),T(te,{key:0,name:s.$t("pos.layout.main.progress_modal.products"),progress:G.value},null,8,["name","progress"])),d.value?C("",!0):(a(),T(te,{key:1,name:s.$t("pos.layout.main.progress_modal.categories"),progress:K.value},null,8,["name","progress"])),k.value?C("",!0):(a(),T(te,{key:2,name:s.$t("pos.layout.main.progress_modal.customers"),progress:p.value},null,8,["name","progress"])),m.value?C("",!0):(a(),T(te,{key:3,name:s.$t("pos.layout.main.progress_modal.orders"),progress:A.value},null,8,["name","progress"]))])])]),_:1},512),l(_,{ref_key:"drawerFormModal",ref:Z},{header:w(({toggle:H})=>[e("div",us,[oe(n(s.$t("pos.layout.main.cash_drawer.title"))+" ",1),e("div",{class:"flex h-6 w-6",onClick:H},r[0]||(r[0]=[e("span",{class:"icon-cross cursor-pointer text-2xl"},null,-1)]),8,ps)])]),content:w(()=>[l(Pe,null,{default:w(({handleSubmit:H})=>[e("form",{class:"grid gap-4",onSubmit:Ie=>H(Ie,$e)},[e("div",gs,[e("label",fs,n(s.$t("pos.layout.main.cash_drawer.opening_drawer_amount")),1),l(g,{type:"text",name:"opening_amount",id:"opening_amount",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required",placeholder:"100",label:s.$t("pos.layout.main.cash_drawer.opening_drawer_amount")},null,8,["label"]),l(B,{name:"opening_amount",class:"text-sm text-red-500"})]),e("div",hs,[e("label",_s,n(s.$t("pos.layout.main.cash_drawer.remarks")),1),l(g,{as:"textarea",name:"remarks",id:"remarks",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all",rules:"required",placeholder:s.$t("pos.layout.main.cash_drawer.remarks_placeholder")},null,8,["placeholder"]),l(B,{name:"remarks",class:"text-sm text-red-500"})]),l(Ce,{type:"submit",isLoading:de.value,class:"secondary-button w-full",icon:"icon-payment",label:s.$t("pos.layout.main.cash_drawer.open_drawer")},null,8,["isLoading","label"])],40,ms)]),_:1})]),_:1},512)]))])}}};export{Cs as default};
