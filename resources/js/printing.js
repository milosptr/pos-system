const ePosDev = new epson.ePOSDevice();
let printer = null;


export const connectToPrinter = () => {
    //Connects to a device
    ePosDev.connect('192.168.1.141', '8008', callback_connect);
}

function callback_connect(resultConnect) {
    console.log('callback_connect')
    if ((resultConnect == 'OK') || (resultConnect == 'SSL_CONNECT_OK')) {
        console.log('callback_connect')
        //Retrieves the Printer object
        ePosDev.createDevice('local_printer', ePosDev.DEVICE_TYPE_PRINTER, {
            'crypto': false,
            'buffer': false
        }, callback_createDevice);
    }
    else {
        //Displays error messages
    }
}

function callback_createDevice(deviceObj, retcode) {
    printer = deviceObj;
    console.log('callback_createDevice')
    if (retcode == 'OK') {
        console.log('callback_createDevice - OK')
        printer.timeout = 60000;
        //Registers an event
        printer.onstatuschange = function (res) { alert(res.success); };
        printer.onbatterystatuschange = function (res) { alert(res.success); };

        printer.addFeedLine(1);
        printer.addTextAlign(printer.ALIGN_CENTER);
        printer.addTextSize(1, 2);
        printer.addTextStyle(false, false, true, printer.COLOR_1);
        printer.addText('TERASA 7\n\n');
        printer.addTextStyle(false, false, false, printer.COLOR_1);
        printer.addTextAlign(printer.ALIGN_LEFT);
        printer.addTextSize(1, 1);
        printer.addText(' Vreme: 22:34\n');
        printer.addTextStyle(false, false, true, printer.COLOR_1);
        printer.addTextSize(1, 2);
        printer.addText(' —————————————————————————————————————————————\n');
        printer.addText(' 2 x Lepinja sa lukom\n');
        printer.addText(' 1 x Šopska salata\n');
        printer.addText(' 2 x Pogača\n');
        printer.addText(' 0,5 x Teleći ražnjić od bifteka\n');
        printer.addText(' —————————————————————————————————————————————\n');
        printer.addFeedLine(1);
        printer.addCut(printer.CUT_FEED);

        if (ePosDev.isConnected) {
          console.log('connect');
          printer.send();
          disconnect()
        }
    } else {
        alert(retcode);
    }
}

function startMonitor() {
    //Starts the status monitoring process
    printer.startMonitor();
}

//Opens the printer cover
function stopMonitor() {
    //Stops the status monitoring process
    printer.stopMonitor();
}

function disconnect() {
    //Discards the Printer object
    ePosDev.deleteDevice(printer, callback_deleteDevice);
}

function callback_deleteDevice(errorCode) {
    //Terminates connection with device
    ePosDev.disconnect();
}
