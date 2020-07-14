<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
	}

	public function index()
	{
		$data = [
			'page' => 'Login Admin'
		];
		$this->load->view('admin/login', $data);
	}
	public function regis()
	{
		$this->form_validation->set_rules('name_user', 'Nama Lengkap', 'required|trim', ['required' => 'Nama Lengkap Tidak Boleh Kosong']);
		$this->form_validation->set_rules('jabatan_user', 'Jabatan', 'required|trim', ['required' => 'Jabatan Tidak Boleh Kosong']);
		$this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[user.username]', ['required' => 'Username Tidak Boleh Kosong'], ['is_unique' => 'Username Sudah Terdaftar']);
		$this->form_validation->set_rules('email_user', 'Email', 'required|trim|valid_email|is_unique[user.email]', ['required' => 'Email Tidak Boleh Kosong', 'is_unique' => 'Email Sudah Terdaftar']);
		$this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[5]|matches[password2]', [
			'required' => 'Password Anda Kosong',
			'matches' => 'Password Tidak Cocok',
			'min_length' => 'Password Terlalu Pendek'
		]);
		$this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');
		if ($this->form_validation->run() == false) {
			$data['title'] = 'Registrasi Admin';
			$this->load->view('admin/auth_header', $data);
			$this->load->view('admin/regis');
			$this->load->view('admin/auth_footer');
		} else {
			$this->load->model('auth_m');
			$this->auth_m->insert();
			$this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">Selamat Admin Telah Terdaftar, Silahkan Login.</div>');
			redirect('auth');
		}
	}

	public function process()
	{
		$post = $this->input->post(null, TRUE);
		if (isset($post['login'])) {
			$this->load->model('auth_m');
			$query = $this->auth_m->login($post);
			if ($query->num_rows() > 0) {
				$data = $query->row();
				$params = array(
					'userid' => $data->id_user,
					'name' => $data->name_user,
					'email' => $data->email_user,
					'jabatan' => $data->jabatan_user
				);
				$this->session->set_userdata($params);
				$this->session->set_flashdata('succes', " Selamat datang, Yth. " . ucfirst($data->name_user));
				redirect('admin/dashboard');
			} else {
				$this->session->set_flashdata('error', "Login Gagal, <br> Harap Periksa Username dan Password !");
				redirect('auth');
			}
		} else {
			$this->session->set_flashdata('warning', "System Error, Please Restart is Browser !");
			redirect('auth');
		}
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('auth');
	}

	public function update()
	{
		$post = $this->input->post(null, TRUE);
		if (isset($post['update'])) {
			$this->load->model('auth_m');
			if ($post['image_remove']) {
				$post['image'] = '';
			} else {
				$config['upload_path']    = './assets/admin/img/user/';
				$config['allowed_types']  = 'jpg|png|jpeg|icon';
				$config['max_size']       = 10000;
				$config['file_name']       = 'user-' . $post['id'];

				$this->load->library('upload', $config);
				$image = $this->upload->do_upload('image');
				if ($image == true) {
					$sql = $this->auth_m->get($post['id'])->row();
					if ($sql->image_user != null) {
						$target_file = './assets/admin/img/user/' . $sql->image_user;
						unlink($target_file);
						$post['image'] = $this->upload->data('file_name');
					} else if ($sql->image_user == null) {
						$post['image'] = $this->upload->data('file_name');
					}
				} else {
					$sql = $this->auth_m->get($post['id'])->row();
					$post['image'] = $sql->image_user;
				}
			}
			$this->auth_m->update($post);
			if ($this->db->affected_rows() > 0) {
				$this->session->set_flashdata('succes', " Profil User Berhasil Diperbaharui ");
				redirect('admin/dashboard');
			} else {
				$this->session->set_flashdata('error', " Profil User Gagal Diperbaharui, Pastikan data yang diisi Benar ");
				redirect('admin/dashboard');
			}
		}
	}
}
