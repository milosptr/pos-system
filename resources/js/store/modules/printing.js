const general = {
  state: () => ({
    printingCategories: [],
    ePosDev: null,
    printer: null,
  }),

  actions: {
    getPrintingCategories({ commit }) {
      axios.get('/api/categories/printing')
        .then((res) => {
          commit('setPrintingCategories', res.data.data)
        })
    },
    setEpsonDevice({ commit }) {
      axios.get('/api/invoices/today')
        .then((res) => {
          commit('setInvoices', res.data.data)
        })
    },
  },

  mutations: {
    setEpsonDevice( state ) {
      state.ePosDev = new epson.ePOSDevice()
      state.ePosDev.connect('192.168.1.141', 8008, (data) => {
        if(data == 'OK' || data == 'SSL_CONNECT_OK') {
          ePosDev.createDevice('local_printer', ePosDev.DEVICE_TYPE_PRINTER,
          {'crypto':false, 'buffer':false}, (devobj, retcode) => {
            if( retcode == 'OK' ) {
              state.printer = devobj;
              state.printer.timeout = 60000;
              state.printer.onreceive = function (res) { alert(res.success); };
              state.printer.oncoveropen = function () { alert('coveropen'); };
            } else {
            console.error(retcode);
            }
          });
        } else {
          console.error(data);
        }
      })
    },
    setPrinterOrder( state, order ) {
      if(state.printer === null) {
        console.error('PRINTER_NOT_SET');
        return
      }
      state.printer.addFeedLine(1);
      state.printer.addTextAlign(printer.ALIGN_CENTER);
      state.printer.addTextSize(1, 2);
      state.printer.addTextStyle(false, false, true, printer.COLOR_1);
      state.printer.addText('TERASA 7\n\n');
      state.printer.addTextStyle(false, false, false, printer.COLOR_1);
      state.printer.addTextAlign(printer.ALIGN_LEFT);
      state.printer.addTextSize(1, 1);
      state.printer.addText(' Vreme: 22:34\n');
      state.printer.addTextStyle(false, true, true, printer.COLOR_1);
      state.printer.addTextSize(1, 2);
      state.printer.addTextPosition(20);
      state.printer.addTextLineSpace(30);
      state.printer.addText(' \n\n');
      state.printer.addTextStyle(false, false, true, printer.COLOR_1);
      state.printer.addText(' 2 x Lepinja sa lukom\n');
      state.printer.addText(' 1 x Šopska salata\n');
      state.printer.addText(' 2 x Pogača\n');
      state.printer.addText(' 0,5 x Teleći ražnjić od bifteka\n');
      state.printer.addTextStyle(false, true, true, printer.COLOR_1);
      state.printer.addTextPosition(20);
      state.printer.addText(' \n');
      state.printer.addFeedLine(1);
      state.printer.addCut(printer.CUT_FEED);
      state.printer.send();
    },
    setPrintingCategories(state, data, rootState, rootGetters) {
      // console.log(rootState);
      state.printingCategories = data.map((c) => c.id)
    }
  },

  getters: {

  }
}

export default general
