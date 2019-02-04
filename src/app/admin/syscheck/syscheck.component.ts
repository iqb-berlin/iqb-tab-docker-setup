import { ConfirmDialogComponent, ConfirmDialogData } from './../../iqb-common/confirm-dialog/confirm-dialog.component';
import { Component, OnInit, ViewChild } from '@angular/core';
import { BackendService, SysCheckStatistics } from './../backend.service';
import { MainDatastoreService } from './../maindatastore.service';
import { MatSnackBar, MatSort, MatTableDataSource, MatDialog } from '@angular/material';
import { SelectionModel } from '@angular/cdk/collections';
import { saveAs } from 'file-saver';


@Component({
  templateUrl: './syscheck.component.html',
  styleUrls: ['./syscheck.component.css']
})
export class SyscheckComponent implements OnInit {
  displayedColumns: string[] = ['selectCheckbox', 'syscheckLabel', 'number', 'details'];
  private resultDataSource = new MatTableDataSource<SysCheckStatistics>([]);
  private isAdmin = false;
  // prepared for selection if needed sometime
  private tableselectionCheckbox = new SelectionModel<SysCheckStatistics>(true, []);
  private dataLoading = false;

  @ViewChild(MatSort) sort: MatSort;

  constructor(
    private bs: BackendService,
    private mds: MainDatastoreService,
    private deleteConfirmDialog: MatDialog,
    public snackBar: MatSnackBar
  ) {
    this.mds.isAdmin$.subscribe(
      i => this.isAdmin = i);
  }

  ngOnInit() {
    this.mds.adminToken$.subscribe(at => this.updateTable());
    this.mds.workspaceId$.subscribe(ws => this.updateTable());
    // console.log(saveAs);
  }

  updateTable() {
    this.dataLoading = true;
    this.tableselectionCheckbox.clear();
    this.bs.getSysCheckReportList(this.mds.adminToken$.getValue(), this.mds.workspaceId$.getValue()).subscribe(
      (resultData: SysCheckStatistics[]) => {
        this.dataLoading = false;
        this.resultDataSource = new MatTableDataSource<SysCheckStatistics>(resultData);
        this.resultDataSource.sort = this.sort;
      }
    )
  }

  isAllSelected() {
    const numSelected = this.tableselectionCheckbox.selected.length;
    const numRows = this.resultDataSource.data.length;
    return numSelected === numRows;
  }

  masterToggle() {
    this.isAllSelected() ?
        this.tableselectionCheckbox.clear() :
        this.resultDataSource.data.forEach(row => this.tableselectionCheckbox.select(row));
  }

  // 444444444444444444444444444444444444444444444444444444444444444444444444444444444444444
  downloadReportsCSV() {
    if (this.tableselectionCheckbox.selected.length > 0) {
      this.dataLoading = true;
      const selectedReports: string[] = [];
      this.tableselectionCheckbox.selected.forEach(element => {
        selectedReports.push(element.id);
      });
      this.bs.getSysCheckReport(
            this.mds.adminToken$.getValue(),
            this.mds.workspaceId$.getValue(),
            selectedReports, ';', '"').subscribe(
      (reportData: string[]) => {
        if (reportData.length > 0) {
          const lineDelimiter = '\n';
          let myCsvData = '';
          reportData.forEach((repLine: string) => {
            myCsvData += repLine + lineDelimiter;
          });
          var blob = new Blob([myCsvData], {type: "text/csv;charset=utf-8"});
          saveAs(blob, "iqb-testcenter-syscheckreports.csv");
        } else {
          this.snackBar.open('Keine Daten verfügbar.', 'Fehler', {duration: 3000});
        }
        this.tableselectionCheckbox.clear();
        this.dataLoading = false;
    })
    }
  }

  deleteReports() {
    if (this.tableselectionCheckbox.selected.length > 0) {
      const selectedReports: string[] = [];
      this.tableselectionCheckbox.selected.forEach(element => {
        selectedReports.push(element.id);
      });

      let prompt = 'Es werden alle Berichte für diese';
      if (selectedReports.length > 1) {
        prompt = prompt + ' ' + selectedReports.length + ' System-Checks ';
      } else {
        prompt = prompt + 'n System-Check "' + selectedReports[0] + '" ';
      }

      const dialogRef = this.deleteConfirmDialog.open(ConfirmDialogComponent, {
        width: '400px',
        data: <ConfirmDialogData>{
          title: 'Löschen von Berichten',
          content: prompt + 'gelöscht. Fortsetzen?',
          confirmbuttonlabel: 'Berichtsdaten löschen'
        }
      });

      dialogRef.afterClosed().subscribe(result => {
        if (result !== false) {
          // =========================================================
          this.dataLoading = true;
          this.bs.deleteSysCheckReports(
                this.mds.adminToken$.getValue(),
                this.mds.workspaceId$.getValue(),
                selectedReports).subscribe((deleteOk: boolean) => {
                  this.tableselectionCheckbox.clear();
                  this.dataLoading = false;
                });
          }
        });
    }
  }
}
