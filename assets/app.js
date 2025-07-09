

// Header (nav bar)

function formController () {
    const forms = document.querySelectorAll("main form")

        forms.forEach(form => {
            if(form.clientWidth < 500) {
                form.classList.add("wrap")
            }else {
                form.classList.remove("wrap")
            }
        })
}

function mainController() {
    const width = window.innerWidth
    const btnOpen = document.querySelector(".header-modifier")
    const header = document.querySelector("header")

    if(width <= 600) {
        btnOpen.classList.add("hide")
        header.classList.add("unactivate")
    }else {
        btnOpen.classList.remove("hide")
    }

    let main = document.querySelectorAll("main")
    main = main[main.length - 1]
    if(header.classList.contains("unactivate")){
        main.classList.add("site-full")
    }else {
        main.classList.remove("site-full")
    }
}

function opener() {

    const btnOpen = document.querySelector(".header-modifier")
    const label = document.querySelector(".searchbar label")
    const header = document.querySelector("header")
    btnOpen?.addEventListener("click", () => {
        header.classList.toggle("unactivate")
        const main = document.querySelector("main")
        if(header.classList.contains("unactivate")){
            main.classList.add("site-full")
        }else {
            main.classList.remove("site-full")
        }

        formController()


    })

    label?.addEventListener("click", () => {
        header.classList.remove("unactivate")
    })
}


function resizeController() {
    window.addEventListener("resize", () => {
        mainController()
        formController()
    })
}

function showSearchStudent() {
    const button = document.querySelector(".float-item button")
    const searchpart = document.querySelector("#site-left")
    button?.addEventListener("click", (e) => {
        searchpart?.classList.add("unhide")
        e.stopPropagation()
    })

    searchpart?.addEventListener("click", (e) => {
        e.stopPropagation()
    })

    document.addEventListener("click", () => {
        searchpart?.classList.remove("unhide")
    })

}

function searchEngine() {
    const searchInput = document.querySelector("#site-left input")
    const students = document.querySelectorAll("#site-left ul a")
    searchInput?.addEventListener("input", (e) => {
        const value = searchInput?.value
        students?.forEach(student => {
            const name = student.querySelector("li span:nth-child(1)").textContent
            if(!name.toLocaleLowerCase().includes(value)) {
                student.classList.add("hide")
            }else {
                student.classList.remove("hide")
                
            }
        })
    })

}

function popupAlert() {
    const div = document.querySelector(".alert-info")
    const hideBtn = document.querySelector(".alert-info i")


    hideBtn?.addEventListener("click", (e) => {
        div?.classList.add("hide")
    })

}   

function filterEngine() {
    const serie = document.querySelector("select[name='serie']")
    const level = document.querySelector("select[name='level']")
    const classe = document.querySelector("select[name='classe']")
    const TRs = document.querySelectorAll(".list-table tbody tr")
    const selects = [{where: "serie", node: serie}, {where: "level", node: level}, {where: "classe", node: classe}]

    selects.forEach((select) => {
        select.node?.addEventListener("change", () => {
            const index = select.node.options.selectedIndex
            const option = select.node.options[index]

            if(select.where == "serie") {
                // We'll filter by filiere

                // We have to find the correct td
                TRs.forEach(tr => {
                    const correctTd = tr.children[1]
                    const text = correctTd.textContent

                    if(!text.toLowerCase().includes(option.textContent.toLowerCase())) {
                        const parent = correctTd.parentNode
                        parent?.classList.add("hide-filiere")
                    }else {
                        const parent = correctTd.parentNode
                        parent?.classList.remove("hide-filiere")
                    }
                })
            }
            if(select.where == "level") {
                // We'll filter by filiere

                // We have to find the correct td
                TRs.forEach(tr => {
                    const correctTd = tr.children[2]
                    const text = correctTd.textContent

                    if(!text.toLowerCase().includes(option.textContent.toLowerCase())) {
                        const parent = correctTd.parentNode
                        parent?.classList.add("hide-level")
                    }else {
                        const parent = correctTd.parentNode
                        parent?.classList.remove("hide-level")

                    }
                })
            }
            if(select.where == "classe") {
                // We'll filter by filiere

                // We have to find the correct td
                TRs.forEach(tr => {
                    const correctTd = tr.children[3]
                    const text = correctTd.textContent

                    if(!text.toLowerCase().includes(option.textContent.toLowerCase())) {
                        const parent = correctTd.parentNode
                        parent?.classList.add("hide-classe")
                    }else {
                        const parent = correctTd.parentNode
                        parent?.classList.remove("hide-classe")

                    }
                })
            }
        })
    })
    //


}




filterEngine()
showSearchStudent()
searchEngine()
popupAlert()
opener()
resizeController()
formController()
mainController()