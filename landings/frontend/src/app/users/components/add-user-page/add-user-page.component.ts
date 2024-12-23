import { Component, OnInit } from '@angular/core';
import { FormBuilder, Validators } from '@angular/forms';
import { userRoleOptions } from '../../userRoleOptions';
import { UsersService } from '../../services/users.service';
import { AlertService } from 'src/app/shared/services/alert.service';

@Component({
  selector: 'app-add-user-page',
  templateUrl: './add-user-page.component.html',
  styleUrls: ['./add-user-page.component.scss'],
})
export class AddUserPageComponent implements OnInit {
  roles = userRoleOptions;

  form = this.fb.group({
    username: ['', Validators.required],
    password: ['', Validators.required],
    firstName: ['', Validators.required],
    lastName: ['', Validators.required],
    role: ['', Validators.required],
  });

  constructor(
    private fb: FormBuilder,
    private usersService: UsersService,
    private alertService: AlertService
  ) {}

  ngOnInit(): void {}

  onSubmit() {
    if (this.form.valid) {
      this.usersService.addUser(
        this.form.value.username!,
        this.form.value.password!,
        this.form.value.firstName!,
        this.form.value.lastName!,
        this.form.value.role!
      );
      this.resetForm();
      this.showAlert();
    }
  }

  resetForm() {
    this.form.reset();
    this.form.patchValue({ role: '' });
  }

  showAlert() {
    this.alertService.showAlertForTime('successAlert', 2000);
  }
}