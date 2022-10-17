const general = {
  state: () => ({
    table: null,
    ePosDev: null,
    printer: null,
    order: [],
    invoice: null,
    tries: 0,
    printing: null,
    printingNotification: false,
    printingAttempts: 0,
  }),

  actions: {
    setEpsonDevice({ dispatch, commit, state }, reprint) {
      if(reprint) state.printingAttempts++
      state.ePosDev = new epson.ePOSDevice()
      state.ePosDev.connect('192.168.200.80', 8008, (data) => {
        console.log('PRINTER_CHECKING');
        if(data == 'OK' || data == 'SSL_CONNECT_OK') {
          console.log('PRINTER_DATA_OK');
          state.ePosDev.createDevice('local_printer', state.ePosDev.DEVICE_TYPE_PRINTER, {'crypto':false, 'buffer':false}, (devobj, retcode) => {
            console.log('PRINTER_createDevice');
            if( retcode == 'OK' ) {
              console.log('PRINTER_createDevice_OK');
              state.printer = devobj;
              state.printer.timeout = 60000;
              state.printer.oncoveropen = function () { console.log('coveropen') }
              state.printer.onreceive = function (res) {
                if(res.success) { // successfull printing
                  commit('resetPrinting')
                } else { // printing error
                  console.log('REPRINTING_ERROR_ONRECEIVE')
                  state.printingNotification = true
                  dispatch('setEpsonDevice', true)
                }
              }
              if(reprint && state.printing) {
                console.log('REPRINTING');
                state.printing.type === 'invoice' ? dispatch('setPrintingInvoice', state.printing.item) : dispatch('setPrintingOrder', state.printing.item)
              }
              console.log('PRINTER_IS_OK');
            } else {
              console.error(retcode)
              }
            });
          } else {
            console.error(data)
          }
        })
    },
    setPrintingOrder({ dispatch, state }, order) {
      state.printingNotification = true
      state.printing = { type: 'order', item: order}
      if(state.printer === null) {
        dispatch('setEpsonDevice', true)
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
        state.printer.addTextLineSpace(55);
        order.order.forEach((o) => {
          if(o.should_print && !o.refund) {
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
    setPrintingInvoice({ dispatch, state }, invoice) {
      state.printingNotification = true
      state.printing = { type: 'invoice', item: invoice}
      if(state.printer === null) {
        dispatch('setEpsonDevice', true)
        console.error('PRINTER_NOT_SET');
        return
      }
      const orders = invoice.order.reverse()

      state.printer.addLayout(state.printer.LAYOUT_RECEIPT, 800, 0, -8, 0, 0, 0);
      state.printer.addTextStyle(false, false, true, state.printer.COLOR_1);
      state.printer.addText('   ================= PREDRAČUN =================\n\n\n');
      state.printer.addText('   Kasir:                                Vlasnik\n');
      state.printer.addText(printerTextBetween('Reon:', invoice.location));
      state.printer.addText(printerTextBetween('Sto broj:', parseInt(invoice.table.table_number).toString()));
      state.printer.addText('   —————————————— PROMET - PRODAJA —————————————\n');
      state.printer.addTextPosition(262);
      state.printer.addText('Artikli\n');
      state.printer.addText('   —————————————————————————————————————————————\n');
      state.printer.addText('   Naziv      Cena      Kol.              Ukupno\n');
      orders.forEach((item) => {
        if(!item.refund) {
          state.printer.addText('   ' + item.name +' (Ђ)/' + item.unit + '\n');
          state.printer.addText(printerItemPriceText(formatPrice(item.price).toString(), parseFloat(item.qty).toString(),formatPrice(item.price * item.qty).toString()));
        }
      })
      state.printer.addText('   =============================================\n');
      state.printer.addTextSize(1, 2);
      if(invoice.discount) {
        const original = invoice.total / 0.85
        const discount = original - invoice.total
        state.printer.addTextSize(1, 1);
        state.printer.addText(printerTextBetween('Iznos bez popusta:', formatPrice(original)));
        state.printer.addText(printerTextBetween('Popust 15%:', formatPrice(discount)));
        state.printer.addTextSize(1, 2);
        state.printer.addText(printerTextBetween('Ukupan iznos:', formatPrice(invoice.total)));
      } else {
        state.printer.addTextSize(1, 2);
        state.printer.addText(printerTextBetween('Ukupan iznos:', formatPrice(invoice.total)));
      }
      state.printer.addTextSize(1, 1);
      state.printer.addText('   =============================================\n');
      state.printer.addText('   Oznaka   Naziv       Stopa              Porez\n');
      state.printer.addText(printerTextBetween('Ђ        О-ПДВ       20% ', invoice.tax));
      state.printer.addText(printerTextBetween('Ukupan iznos poreza:', invoice.tax));
      state.printer.addText('   =============================================\n');
      state.printer.addText(printerTextBetween('Vreme:', invoice.created_at));

      const counter = (3323 + invoice.id) + '/' + (3561 + invoice.id)
      state.printer.addText(printerTextBetween('Brojač računa:', counter));
      state.printer.addFeedLine(3);
      state.printer.addCut(state.printer.CUT_FEED);
      state.printer.send();
      console.log('PRINTING_DONE');
    },
  },

  mutations: {
    resetPrinting(state) {
      state.printingAttempts = 0
      state.printingNotification = false
      state.printing = null
    },
    setPrintingNotification(state, value) {
      state.printingNotification = value
    }
  },

  getters: {
    printingNotification: (state) => state.printingNotification,
    printingAttempts: (state) => state.printingAttempts,
    printingType: (state) => state.printing?.type,
  }
}

// Helper functions
const printerTextBetween = (left, right) => {
  let l = left ? left : ''
  let r = right ? right : ''
  const spaces = 45 - l.toString().length - r.toString().length
  return '   ' + l + " ".repeat(spaces) + r + '\n'
}

const printerItemPriceText = (price, qty, total) => {
  const startSpaces = 18 - price.length // -3 is for trailing zeros
  const afterPriceSpaces = 6
  const afterQtySpaces = 24 - qty.length - total.length // -3 is for trailing zeros
  return " ".repeat(startSpaces) + price + " ".repeat(afterPriceSpaces) + qty + " ".repeat(afterQtySpaces) + total + '\n'
}

const formatPrice = (value) => {
  if(!value)
    return 0
  return parseFloat(value).toFixed(0).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.').toString() + ',00'
}

export default general
