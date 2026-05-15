import{g as y,r,k as F,s as h,E as x,o as i,z as v,f as L,c as p,F as _,A as C,G as k,d as n,n as m,t as g,b as s,H as A}from"./index-C5hIcM1_.js";import{u as w}from"./window-BcH-t1wI.js";const z=y`
    mutation agentLogin($input: AgentLoginInput!) {
        agentLogin(input: $input) {
            success
            errors
            message
            accessToken
            agent {
                id
                outletId
                username
                firstName
                lastName
                email
                image
                imageUrl
                reportType
                status
                createdAt
                updatedAt
                outlet {
                    id
                    name
                    email
                    phone
                    website
                    customerCareNumber
                    gstNumber
                    lowStockQty
                    address
                    country
                    state
                    city
                    postcode
                    status
                    receiptId
                    inventorySourceId
                    createdAt
                    updatedAt
                    receipt {
                        id
                        title
                        status
                        displayOutletName
                        showOrderBarcode
                        showPrintConfirmation
                        displayDate
                        displayOrderId
                        orderIdLabel
                        displayCustomerName
                        displaySubTotal
                        subTotalLabel
                        displayDiscount
                        discountLabel
                        displayTax
                        taxLabel
                        displayCreditAmount
                        creditAmountLabel
                        displayChangeAmount
                        creditChangeLabel
                        displayCashierName
                        cashierLabel
                        displayOutletAddress
                        grandTotalLabel
                        displayLogo
                        logo
                        logoUrl
                        logoWidth
                        logoHeight
                        logoAlt
                        headerContent
                        footerContent
                        createdAt
                        updatedAt
                    }
                }
                banks {
                    id
                    name
                }
            }
        }
    }
`,G=y`
    mutation agentLogout {
        agentLogout {
            success
            message
        }
    }
`,T={class:"flex items-center gap-2.5"},N={class:"text-base leading-5"},O=["onClick"],U={__name:"FlashMessage",setup(I){const f=r(0),o=r([]),c=F("emitter"),{isMobileOrTab:t}=w(),d=r({info:{background:"#0284C7",color:"#FFFFFF",icon:"icon-info"},success:{background:"#059669",color:"#FFFFFF",icon:"icon-check"},warning:{background:"#FACC15",color:"#1F2937",icon:"icon-warning"},error:{background:"#EF4444",color:"#FFFFFF",icon:"icon-cancel"}}),b=e=>{e.uid=f.value++,o.value.push(e),setTimeout(()=>{u(e)},5e3)},u=e=>{let l=o.value.indexOf(e);l!=-1&&o.value.splice(l,1)};return h(()=>{c.on("add_flash",b)}),x(()=>{c.off("add_flash")}),(e,l)=>(i(),v(A,{tag:"div",name:"flash-group","enter-from-class":s(t)?"translate-y-full opacity-0":"ltr:translate-x-full rtl:-translate-x-full opacity-0","enter-active-class":"transform transition duration-300 ease-in-out","enter-to-class":s(t)?"translate-y-0 opacity-100":"ltr:translate-x-0 rtl:-translate-x-0 opacity-100","leave-from-class":s(t)?"translate-y-0 opacity-100":"ltr:translate-x-0 rtl:-translate-x-0 opacity-100","leave-active-class":"transform transition duration-300 ease-in-out","leave-to-class":s(t)?"translate-y-0 scale-95 opacity-0":"ltr:translate-x-full rtl:-translate-x-full opacity-0",class:m(["fixed z-[10003] grid gap-2.5 justify-items-end",s(t)?"bottom-5 justify-center px-5":"top-5 ltr:right-5 rtl:left-5"])},{default:L(()=>[(i(!0),p(_,null,C(o.value,a=>(i(),p("div",{style:k(d.value[a.type]),class:"mb-2 flex w-full justify-between gap-12 rounded-lg p-3 shadow-md",key:a.uid},[n("div",T,[n("span",{class:m([d.value[a.type].icon,"cursor-pointer text-2xl leading-6"])},null,2),n("span",N,g(a.message),1)]),n("span",{class:"cursor-pointer whitespace-nowrap text-base leading-6 underline",onClick:B=>u(a)},g(e.$t("pos.common.flash_messages.close")),9,O)],4))),128))]),_:1},8,["enter-from-class","enter-to-class","leave-from-class","leave-to-class","class"]))}};export{z as L,U as _,G as a};
