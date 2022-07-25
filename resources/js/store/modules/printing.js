const general = {
  state: () => ({
    table: null,
    ePosDev: null,
    printer: null,
    order: [],
    invoice: null,
    tries: 0,
  }),

  actions: {},

  mutations: {
    setEpsonDevice( state ) {
      state.ePosDev = new epson.ePOSDevice()
      state.ePosDev.connect('192.168.1.141', 8008, (data) => {
        console.log('PRINTER_CHECKING');
        if(data == 'OK' || data == 'SSL_CONNECT_OK') {
          console.log('PRINTER_DATA_OK');
          state.ePosDev.createDevice('local_printer', state.ePosDev.DEVICE_TYPE_PRINTER,
          {'crypto':false, 'buffer':false}, (devobj, retcode) => {
            console.log('PRINTER_createDevice');
            if( retcode == 'OK' ) {
              console.log('PRINTER_createDevice_OK');
              state.printer = devobj;
              state.printer.timeout = 60000;
              state.printer.onreceive = function (res) { alert(res.success); }; // if printing fails success: false
              state.printer.oncoveropen = function () { alert('coveropen'); };
              console.log('PRINTER_IS_OK');
            } else {
            console.error(retcode);
            }
          });
        } else {
          console.error(data);
        }
      })
    },
    setPrintingOrder(state, order) {
      if(state.printer === null) {
        console.error('PRINTER_NOT_SET');
        return
      }
      if(order.order.some((o) => o.should_print)) {
        console.log('PRINTING_IN_PROGRESS');
        state.printer.addTextAlign(state.printer.ALIGN_CENTER);
        state.printer.addTextSize(1, 2);
        state.printer.addTextStyle(false, false, true, state.printer.COLOR_1);
        state.printer.addText(`${order.table_name}\n\n`);
        state.printer.addTextStyle(false, false, false, state.printer.COLOR_1);
        state.printer.addTextAlign(state.printer.ALIGN_LEFT);
        state.printer.addTextSize(1, 1);
        state.printer.addText(` Vreme: ${order.time}\n`);
        state.printer.addTextStyle(false, false, true, state.printer.COLOR_1);
        state.printer.addTextSize(1, 2);
        state.printer.addText(' —————————————————————————————————————————————\n');
        state.printer.addTextLineSpace(50);
        order.order.forEach((o) => {
          if(o.should_print) {
            state.printer.addText(` ${o.qty} x ${o.name}\n`);
          }
        })
        state.printer.addText(' —————————————————————————————————————————————\n');
        state.printer.addFeedLine(1);
        state.printer.addCut(state.printer.CUT_FEED);
        state.printer.send();
        console.log('PRINTING_DONE');
      }
    },
    setPrintingInvoice(state, invoice) {
      if(state.printer === null) {
        console.error('PRINTER_NOT_SET');
        return
      }
      state.printer.addLayout(state.printer.LAYOUT_RECEIPT, 800, 0, 0, 0, 0, 0);
      state.printer.addTextStyle(false, false, true, state.printer.COLOR_1);
      state.printer.addTextVPosition(8);
      state.printer.addTextPosition(20);
      state.printer.addText('___\n');
      state.printer.addTextVPosition(13);
      state.printer.addTextPosition(20);
      state.printer.addText('___');
      state.printer.addTextPosition(260);
      state.printer.addTextVPosition(22);
      state.printer.addText('RAČUN');
      state.printer.addTextVPosition(8);
      state.printer.addText(' ___\n');
      state.printer.addTextVPosition(13);
      state.printer.addTextPosition(320);
      state.printer.addText(' ___\n\n\n');
      state.printer.addText(' Kasir: Srdjan\n');
      state.printer.addText(' Reon: Basta\n');
      state.printer.addText(' Sto broj: 11\n');
      state.printer.addTextPosition(20);
      state.printer.addText('—————————————— PROMET - PRODAJA —————————————\n');
      state.printer.addTextPosition(246);
      state.printer.addText('Artikli\n');
      state.printer.addTextPosition(20);
      state.printer.addText('_\n\n');
      state.printer.addText(' Naziv Cena Kol. Ukupno\n');
      state.printer.addText(' Min. voda flašica (Ђ)/КОМ.\n ');
      state.printer.addText(' 80,00 1 80,00\n');
      state.printer.addText(' Lepinja sa lukom (Ђ)/КОМ.\n');
      state.printer.addText(' 140,00 1 140,00\n');
      state.printer.addText(' Lepinja sa lukom (Ђ)/КОМ.\n');
      state.printer.addText(' 140,00 1 140,00\n');
      state.printer.addText(' Min. voda flašica (Ђ)/КОМ.\n ');
      state.printer.addText('—————————————————————————————————————\n');
      state.printer.addTextLineSpace(30);
      state.printer.addTextPosition(20);
      state.printer.addText('_\n\n');
      state.printer.addTextSize(1, 2);
      state.printer.addText(' Ukupan iznos: 1.200,00\n');
      state.printer.addTextSize(1, 1);
      state.printer.addTextLineSpace(20);
      state.printer.addText(' Gotovina: 1.200,00\n');
      state.printer.addTextLineSpace(5);
      state.printer.addTextPosition(20);
      state.printer.addText('_\n');
      state.printer.addTextLineSpace(30);
      state.printer.addTextPosition(20);
      state.printer.addTextLineSpace(20);
      state.printer.addText('_\n\n');
      state.printer.addTextLineSpace(30);
      state.printer.addText(' Oznaka Naziv Stopa Porez\n');
      state.printer.addText(' Ђ О-ПДВ 20% 186,67\n');
      state.printer.addTextLineSpace(20);
      state.printer.addText(' Ukupan iznos poreza: 186,67\n');
      state.printer.addTextPosition(20);
      state.printer.addTextLineSpace(5);
      state.printer.addText('_\n');
      state.printer.addTextPosition(20);
      state.printer.addTextLineSpace(20);
      state.printer.addText('_\n\n');
      state.printer.addTextLineSpace(30);
      state.printer.addText(' Vreme: 18.07.2022 21:00:29\n');
      state.printer.addText(' Brojač računa: 3323/3561\n');
      state.printer.addFeedLine(1);
      state.printer.addCut(state.printer.CUT_FEED);
      state.printer.send();
    },
  },

  getters: {

  }
}

export default general
