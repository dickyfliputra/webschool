<?php defined('BASEPATH') or exit('No direct script access allowed');

class Auth_m extends CI_Model
{
     public function login($post)
     {
          $this->db->from('tb_user');
          $this->db->where('username', $post['u_name']);
          $this->db->where('password', sha1($post['u_pass']));
          $query = $this->db->get();
          return $query;
     }

     public function get($id)
     {
          $this->db->from('tb_user');
          $this->db->where('id_user', $id);
          $query = $this->db->get();
          return $query;
     }

     public function update($post)
     {
          $params = [
               'name_user' => $post['u_name'],
               'jabatan_user' => $post['u_jabatan'],
               'username' => $post['u_username'],
               'email_user' => $post['u_mail'],
               'image_user' => $post['image'],
               'updated_user' => date('Y-m-d H:i:s')
          ];

          if ($post['u_password'] != null) {
               $params['password'] = sha1($post['u_password']);
          }

          $this->db->where('id_user', $post['id']);
          $this->db->update('tb_user', $params);
     }
     public function insert($data)
     {
          $data = [
               'id_user' => random_string('alnum', 5),
               'name_user' => htmlspecialchars($this->input->post('name_user'), true),
               'jabatan_user' => htmlspecialchars($this->input->post('jabatan_user'), true),
               'username' => htmlspecialchars($this->input->post('username'), true),
               'password' => password_hash($this->input->post('password1'),),
               'email_user' => htmlspecialchars($this->input->post('email'), true),
               'image_user' => 'default.png',
               'status_user' => 1,
               'is_active' => 1,
               'updated_user' => time()

          ];
          // $this->db->from('tb_user');
          $this->db->insert('tb_user', $data);
     }
}
