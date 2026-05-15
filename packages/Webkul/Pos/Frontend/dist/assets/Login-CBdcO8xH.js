import{g as k,r as y,u as S,w as B,a as C,b as T,c as g,d as s,t as a,e as n,f as E,h,o as p,n as L,i as N,j as F,k as O,l as Q,m as U}from"./index-C5hIcM1_.js";import{_ as H,L as j}from"./FlashMessage-Ca8WSK6k.js";import"./window-BcH-t1wI.js";const G="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACIAAAAwCAYAAAB0WahSAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAHSSURBVHgB7Zi7TsMwFIZ/J5EoElLdMjAgQcQTdIGFpWWEBVYWWNkQL4AYmeAN6BtUPEEyMCIoKwthY2sqMbY1ToOU5OTSXNrC4E+y5LrH9pf42JHMUAluBnXXwXLhbej8AXpjIIuIFK3xKv87jwrOX6AlJ7NikyeXAbTmdZHRWa4onZ/J0HvPBsWwMRYnctncWYGzRXyJbqxdwAUTtqy5U0EBE4y1EuL6mIhOHpkM5FrH8+DjNw94cjzvxvoYzTtUQvcmJcmYKEDQ+GVcRiZ5OQn51PRN5JFIl7FQCr3Rq/xEOrfyjqFlDHMcqtsYuTaKwnAT+T1BC8VEiLkQjyiDL++GxNooJKKT80KTW7AsAk5QZ9soJEIPrhHKnwFMhN9IarIzHIietI6u3VeX4/0q6LT77GB1B6V46XB8v/lj1baAvb6TFGbIBOJSx4zqrUSjmGGiLPoabTGTwrJ2zVJRIhQlQlm8iFHPFZYsUt8P6t7e90pZeGis9cPUMCOx1T94gOGT7HyESmxeACtyvPEQ2DhNDZOfIWFlfYyWhdo1FCVCUSIUJUJRIhQlQlEiFCVCUSIUA5+3eS85FwqbXi+BtfHHqByh/BsRNr07rRW+Y587P6+fgP384iOuAAAAAElFTkSuQmCC",J=k`
    query fetchGlobalData {
        fetchGlobalData {
            locales {
                code
                name
                direction
            }
            currencies {
                id
                code
                name
                symbol
                decimal
                groupSeparator
                decimalSeparator
                currencyPosition
            }
            configurations {
                code
                value
            }
            countries {
                id
                code
                name
            }
            countryStates {
                countryCode
                states {
                    id
                    countryId
                    countryCode
                    code
                    defaultName
                }
            }
            baseCurrency
            defaultLocale
        }
    }
`,M={key:0,class:"flex justify-center items-center h-screen"},P={key:1,class:"mx-auto"},R={class:"mt-12 grid justify-center gap-10 max-w-[370px] mx-auto"},Y={class:"grid justify-items-center gap-2.5"},q={class:"flex h-[74px] w-[74px] items-center justify-center rounded-full bg-white"},D=["src"],X={key:1,src:G,alt:"POS Logo",width:"34",height:"46"},V={class:"text-3xl font-semibold text-dark truncate max-w-full"},W=["onSubmit"],$={class:"text-2xl font-semibold leading-7 truncate"},K={class:"grid gap-1"},Z={for:"username",class:"text-base font-semibold leading-5 text-dark"},z={class:"grid gap-1"},ee={for:"password",class:"text-base font-semibold leading-5 text-dark"},se={class:"relative"},te={for:"remember",class:"flex items-center gap-2 text-base font-normal leading-5 text-dark"},oe={type:"submit",class:"primary-button"},re={class:"mx-32"},ae={class:"mb-5 mt-10 flex flex-col gap-3.5 text-center"},ne=["innerHTML"],le={class:"text-sm font-normal leading-4 text-dark"},me={__name:"Login",setup(ie){const _=F(),d=Q(),x=O("emitter"),l=U(),u=y(!1),A=y([]),{result:f,loading:v}=S(J);B(()=>{var e;return(e=f==null?void 0:f.value)==null?void 0:e.fetchGlobalData},e=>{e&&(A.value=e.configurations,parseInt(r("status"))||_.push({path:"/404"}),d.updateItem("configurations",{id:1,configurations:e.configurations}),d.updateItem("locales_currencies",{id:1,currencies:e.currencies,locales:e.locales}),d.updateItem("countries_states",{id:1,countries:e.countries,countryStates:e.countryStates}),l.set("locale",JSON.stringify(e.locales.find(t=>t.code===e.defaultLocale))),l.set("currency",JSON.stringify(e.currencies.find(t=>t.code===e.baseCurrency))))});const{mutate:w}=C(j),I=(e,{setErrors:t,resetForm:i})=>{w({input:{username:e.username,password:e.password,remember:!!e.remember}}).then(async c=>{var m;const o=(m=c==null?void 0:c.data)==null?void 0:m.agentLogin;(o==null?void 0:o.success)===!0?(localStorage.setItem("accessToken",o.accessToken),localStorage.setItem("loginMessage",o.message),d.deleteAllItems("agents"),d.addItem("agents",o.agent),l.remove("products_fetched"),l.remove("customers_fetched"),l.remove("orders_fetched"),l.remove("categories_fetched"),_.push({path:"/home"})):o.success===!1&&(o.errors?t(JSON.parse(o.errors)):x.emit("add_flash",{type:"error",message:o.message}))})},r=e=>{var t;return(t=A.value.find(i=>i.code===`pos.settings.general.${e}`))==null?void 0:t.value};return(e,t)=>{const i=h("v-field"),c=h("v-error-message"),o=h("v-form");return T(v)?(p(),g("div",M,t[1]||(t[1]=[s("div",{class:"flex gap-2"},[s("div",{class:"w-5 h-5 rounded-full animate-pulse bg-blue-600"}),s("div",{class:"w-5 h-5 rounded-full animate-pulse bg-blue-600"}),s("div",{class:"w-5 h-5 rounded-full animate-pulse bg-blue-600"})],-1)]))):(p(),g("div",P,[s("div",R,[s("div",Y,[s("div",q,[r("pos_logo")?(p(),g("img",{key:0,src:r("pos_logo"),alt:"POS Logo",width:"34",height:"46"},null,8,D)):(p(),g("img",X))]),s("h1",V,a(r("heading_on_login")??e.$t("pos.title")),1)]),n(o,null,{default:E(({handleSubmit:m})=>[s("form",{class:"grid gap-6 rounded-2xl bg-white p-9",onSubmit:b=>m(b,I)},[s("h2",$,a(r("sub_heading_on_login")??e.$t("pos.login_form.title")),1),s("div",K,[s("label",Z,a(e.$t("pos.login_form.user_name")),1),n(i,{type:"text",name:"username",id:"username",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-3 transition-all",rules:"required",placeholder:e.$t("pos.login_form.user_name_placeholder")},null,8,["placeholder"]),n(c,{name:"username",class:"text-sm text-red-500"})]),s("div",z,[s("label",ee,a(e.$t("pos.login_form.password")),1),s("div",se,[n(i,{type:u.value?"text":"password",name:"password",id:"password",class:"text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish py-3 pl-2.5 pr-10 transition-all",rules:"required",placeholder:e.$t("pos.login_form.password_placeholder")},null,8,["type","placeholder"]),s("span",{class:L([u.value?"icon-eye":"icon-eye-off","absolute right-2.5 top-3.5 cursor-pointer text-2xl leading-6 opacity-60"]),onClick:t[0]||(t[0]=b=>u.value=!u.value)},null,2)]),n(c,{name:"password",class:"text-sm text-red-500"})]),s("label",te,[n(i,{type:"checkbox",name:"remember",id:"remember",class:"peer hidden",value:"false"}),t[2]||(t[2]=s("span",{class:"icon-un-checked peer-checked:icon-checked cursor-pointer rounded-md text-2xl"},null,-1)),N(" "+a(e.$t("pos.login_form.remember_password")),1)]),s("button",oe,a(e.$t("pos.login_form.login_btn_title")),1)],40,W)]),_:1})]),s("footer",re,[s("div",ae,[s("p",{class:"text-sm font-normal leading-4 text-dark",innerHTML:r("footer_content")??e.$t("pos.footer.warning")},null,8,ne),s("p",le,a(r("footer_note")??e.$t("pos.footer.copyright")),1)])]),n(H)]))}}};export{me as default};
